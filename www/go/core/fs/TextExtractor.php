<?php

namespace go\core\fs;

use ZipArchive;

/**
 * Extracts the plain text of office documents and PDF files
 *
 * Supported are Word (docx), Excel (xlsx), PowerPoint (pptx) and OpenDocument text, spreadsheet and presentation files
 * (odt, ods, odp). These are zip files with XML inside and are read with PHP only. PDF files are read with pdftotext
 * from poppler-utils when it is installed.
 *
 * The format is detected from the content, not from the file name.
 *
 * The XML is not parsed. Tags are replaced by line breaks and tabs where they end a paragraph, cell or row and then
 * removed. Entities other than the XML ones and character references are not resolved, so a document can't make it
 * read other files (XXE) or expand entities without end.
 *
 * Zip entries are read in a stream up to a limit, because the size in the zip can't be trusted (zip bombs).
 *
 * Spreadsheets are returned as tab separated rows per sheet. Dates are returned as stored, in Excel files that is a
 * number.
 *
 * @example
 * ```
 * $extractor = new TextExtractor();
 * $text = $extractor->extract($blob->path());
 * if ($extractor->truncated) {
 *   // Only the first part was read
 * }
 * ```
 */
class TextExtractor
{
	/**
	 * Maximum number of bytes read from one zip entry or from the output of pdftotext
	 */
	const MAX_ENTRY_SIZE = 20971520;

	/**
	 * Maximum number of bytes read from all entries of a zip file together
	 */
	const MAX_TOTAL_SIZE = 52428800;

	/**
	 * Maximum number of slides or sheets that are read
	 */
	const MAX_PARTS = 1000;

	/**
	 * Seconds pdftotext may run
	 */
	const PDF_TIMEOUT = 30;

	/**
	 * Formats that {@see extract()} can read
	 */
	const FORMATS = ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp', 'pdf'];

	private const ODF_TYPES = [
		'application/vnd.oasis.opendocument.text' => 'odt',
		'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
		'application/vnd.oasis.opendocument.presentation' => 'odp',
	];

	/**
	 * Maximum number of tabs for a repeated empty cell in an OpenDocument spreadsheet
	 */
	private const MAX_REPEATED_CELLS = 100;

	/**
	 * Maximum number of columns of a row in an Excel sheet
	 */
	private const MAX_COLUMNS = 1000;

	/**
	 * True when a limit was reached and only the first part of the file was read
	 */
	public bool $truncated = false;

	private int $bytesRead = 0;

	/**
	 * @param int $maxEntrySize Maximum number of bytes read from one zip entry or from pdftotext
	 * @param int $maxTotalSize Maximum number of bytes read from all zip entries together
	 */
	public function __construct(private readonly int $maxEntrySize = self::MAX_ENTRY_SIZE, private readonly int $maxTotalSize = self::MAX_TOTAL_SIZE)
	{
	}

	/**
	 * Detect the format of a file from its content
	 *
	 * @return string|null One of {@see FORMATS}, "zip" for another zip file or null for anything else
	 */
	public static function detectFormat(string $path): ?string
	{
		$handle = @fopen($path, 'rb');
		if ($handle === false) {
			return null;
		}
		$head = (string) fread($handle, 5);
		fclose($handle);

		if ($head === '%PDF-') {
			return 'pdf';
		}
		if (!str_starts_with($head, "PK\x03\x04")) {
			return null;
		}

		$zip = self::openZip($path);
		if (!$zip) {
			return null;
		}
		try {
			$stream = $zip->getStream('mimetype');
			if ($stream !== false) {
				$mimetype = trim((string) fread($stream, 100));
				fclose($stream);
				// Templates like "application/vnd.oasis.opendocument.text-template" are read the same way
				foreach (self::ODF_TYPES as $type => $format) {
					if ($mimetype === $type || $mimetype === $type . '-template') {
						return $format;
					}
				}
			}
			if ($zip->locateName('word/document.xml') !== false) {
				return 'docx';
			}
			if ($zip->locateName('xl/workbook.xml') !== false) {
				return 'xlsx';
			}
			if ($zip->locateName('ppt/presentation.xml') !== false) {
				return 'pptx';
			}
			return 'zip';
		} finally {
			$zip->close();
		}
	}

	/**
	 * Extract the text of a file
	 *
	 * @return string The text, empty when the document has no text, like a scanned PDF
	 * @throws TextExtractorException When the format is not supported or the file can't be read
	 */
	public function extract(string $path): string
	{
		$this->truncated = false;
		$this->bytesRead = 0;

		$format = self::detectFormat($path);
		if ($format === 'pdf') {
			return $this->extractPdf($path);
		}
		if ($format === null || !in_array($format, self::FORMATS, true)) {
			throw new TextExtractorException('The format of the file is not supported');
		}

		$zip = self::openZip($path);
		if (!$zip) {
			throw new TextExtractorException('The file could not be opened');
		}
		try {
			$text = match ($format) {
				'docx' => $this->extractDocx($zip),
				'xlsx' => $this->extractXlsx($zip),
				'pptx' => $this->extractPptx($zip),
				default => $this->extractOdf($zip, $format),
			};
		} finally {
			$zip->close();
		}
		return self::normalize($text);
	}

	/**
	 * Read one entry of a zip file as it is stored
	 *
	 * @param string $path Path of the zip file
	 * @param string $entry Name of the entry, like "word/document.xml"
	 * @return string|null The content, at most the maximum entry size, or null when the zip has no such entry
	 * @throws TextExtractorException When the file is not a zip file
	 */
	public function readEntry(string $path, string $entry): ?string
	{
		$this->truncated = false;
		$this->bytesRead = 0;

		$zip = self::openZip($path);
		if (!$zip) {
			throw new TextExtractorException('The file is not a zip file');
		}
		try {
			if ($zip->locateName($entry) === false) {
				return null;
			}
			return $this->readZipEntry($zip, $entry);
		} finally {
			$zip->close();
		}
	}

	/**
	 * Get the names of the entries in a zip file
	 *
	 * @param int $limit Maximum number of names
	 * @return string[] Empty when the file is not a zip file
	 */
	public static function listEntries(string $path, int $limit = 200): array
	{
		$zip = self::openZip($path);
		if (!$zip) {
			return [];
		}
		try {
			$names = [];
			for ($i = 0, $count = min($zip->numFiles, $limit); $i < $count; $i++) {
				$name = $zip->getNameIndex($i);
				if ($name !== false) {
					$names[] = $name;
				}
			}
			return $names;
		} finally {
			$zip->close();
		}
	}

	private static function openZip(string $path): ?ZipArchive
	{
		if (!is_file($path)) {
			return null;
		}
		$zip = new ZipArchive();
		return $zip->open($path, ZipArchive::RDONLY) === true ? $zip : null;
	}

	/**
	 * Read an entry in a stream, so the uncompressed size is checked while reading and not taken from the zip
	 */
	private function readZipEntry(ZipArchive $zip, string $name): string
	{
		$limit = min($this->maxEntrySize, $this->maxTotalSize - $this->bytesRead);
		if ($limit <= 0) {
			$this->truncated = true;
			return '';
		}

		$stream = $zip->getStream($name);
		if ($stream === false) {
			throw new TextExtractorException('The file "' . $name . '" in the zip could not be read');
		}
		try {
			$data = '';
			while (!feof($stream) && strlen($data) <= $limit) {
				$chunk = fread($stream, 65536);
				if ($chunk === false || $chunk === '') {
					break;
				}
				$data .= $chunk;
			}
		} finally {
			fclose($stream);
		}

		if (strlen($data) > $limit) {
			$data = substr($data, 0, $limit);
			$this->truncated = true;
		}
		$this->bytesRead += strlen($data);
		return $data;
	}

	/**
	 * Read an entry when it exists
	 */
	private function readOptionalEntry(ZipArchive $zip, string $name): ?string
	{
		return $zip->locateName($name) === false ? null : $this->readZipEntry($zip, $name);
	}

	private function extractDocx(ZipArchive $zip): string
	{
		return self::ooxmlToText($this->readZipEntry($zip, 'word/document.xml'));
	}

	private function extractPptx(ZipArchive $zip): string
	{
		$slides = $this->getPartsInOrder($zip, 'ppt/presentation.xml', 'sldId');
		if (empty($slides)) {
			// Without a valid presentation.xml, take the slide files in their natural order
			$slides = [];
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$name = (string) $zip->getNameIndex($i);
				if (preg_match('#^ppt/slides/slide\d+\.xml$#', $name)) {
					$slides[] = $name;
				}
			}
			natsort($slides);
		}

		$text = '';
		$number = 0;
		foreach ($slides as $slide) {
			if ($number == self::MAX_PARTS) {
				$this->truncated = true;
				break;
			}
			$number++;
			$xml = $this->readOptionalEntry($zip, $slide);
			if ($xml !== null) {
				$text .= '--- Slide ' . $number . " ---\n" . self::ooxmlToText($xml) . "\n\n";
			}
		}
		return $text;
	}

	private function extractXlsx(ZipArchive $zip): string
	{
		$sharedStrings = [];
		$xml = $this->readOptionalEntry($zip, 'xl/sharedStrings.xml');
		if ($xml !== null) {
			preg_match_all('#<si\b[^>]*?(?:/>|>(.*?)</si>)#s', $xml, $matches, PREG_SET_ORDER);
			foreach ($matches as $si) {
				$sharedStrings[] = self::xlsxString($si[1] ?? '');
			}
		}

		$names = [];
		$sheets = $this->getPartsInOrder($zip, 'xl/workbook.xml', 'sheet', $names);

		$text = '';
		$count = 0;
		foreach ($sheets as $i => $sheet) {
			if ($count == self::MAX_PARTS) {
				$this->truncated = true;
				break;
			}
			$count++;
			$xml = $this->readOptionalEntry($zip, $sheet);
			if ($xml === null) {
				continue;
			}
			$text .= '## ' . ($names[$i] ?? 'Sheet ' . $count) . "\n" . self::xlsxSheetToText($xml, $sharedStrings) . "\n";
		}
		return $text;
	}

	/**
	 * The text of a shared or inline string. Phonetic runs (rPh) are left out.
	 */
	private static function xlsxString(string $xml): string
	{
		$xml = (string) preg_replace('#<rPh\b.*?</rPh>#s', '', $xml);
		preg_match_all('#<t\b[^>]*?(?:/>|>(.*?)</t>)#s', $xml, $matches, PREG_SET_ORDER);
		return self::decode(implode('', array_map(fn($t) => $t[1] ?? '', $matches)));
	}

	/**
	 * Tab separated rows of a sheet. Cells are placed in their column with the "r" attribute, like "C3".
	 *
	 * @param string[] $sharedStrings
	 */
	private static function xlsxSheetToText(string $xml, array $sharedStrings): string
	{
		$lines = [];
		preg_match_all('#<row\b[^>]*?(?:/>|>(.*?)</row>)#s', $xml, $rows, PREG_SET_ORDER);
		foreach ($rows as $rowMatch) {
			$row = $rowMatch[1] ?? '';
			$cells = [];
			$next = 0;
			preg_match_all('#<c\b([^>]*?)(?:/>|>(.*?)</c>)#s', $row, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$attributes = $match[1];
				$content = $match[2] ?? '';

				$column = $next;
				if (preg_match('#\br="([A-Z]+)\d*"#', $attributes, $ref)) {
					$column = self::columnIndex($ref[1]);
				}
				if ($column >= self::MAX_COLUMNS) {
					continue;
				}
				$next = $column + 1;

				$type = preg_match('#\bt="([a-zA-Z]+)"#', $attributes, $t) ? $t[1] : 'n';
				if ($type === 'inlineStr') {
					$value = self::xlsxString($content);
				} else {
					$value = preg_match('#<v\b[^>]*>(.*?)</v>#s', $content, $v) ? self::decode($v[1]) : '';
					if ($type === 's') {
						$value = $sharedStrings[(int) $value] ?? '';
					} else if ($type === 'b') {
						$value = $value === '1' ? 'TRUE' : 'FALSE';
					}
				}
				// A tab or line break would break the row
				$cells[$column] = str_replace(["\r\n", "\n", "\r", "\t"], ' ', $value);
			}

			if (empty($cells)) {
				$lines[] = '';
				continue;
			}
			$line = [];
			for ($i = 0, $max = max(array_keys($cells)); $i <= $max; $i++) {
				$line[] = $cells[$i] ?? '';
			}
			$lines[] = implode("\t", $line);
		}
		return implode("\n", $lines);
	}

	/**
	 * Column letters to a zero based index. "A" is 0, "Z" is 25 and "AA" is 26.
	 */
	private static function columnIndex(string $letters): int
	{
		$index = 0;
		foreach (str_split($letters) as $letter) {
			$index = $index * 26 + (ord($letter) - 64);
		}
		return $index - 1;
	}

	/**
	 * Get the parts of a workbook or presentation in their order
	 *
	 * The main part lists elements like <sheet name="Sheet1" r:id="rId1"/>. The relationships file maps the ID to
	 * the file in the zip.
	 *
	 * @param string $mainPart Like "xl/workbook.xml"
	 * @param string $element The element that refers to a part, like "sheet" or "sldId"
	 * @param string[] $names Set to the "name" attributes, with the same keys as the result
	 * @return string[] Paths in the zip
	 */
	private function getPartsInOrder(ZipArchive $zip, string $mainPart, string $element, array &$names = []): array
	{
		$xml = $this->readOptionalEntry($zip, $mainPart);
		$dir = dirname($mainPart);
		$rels = $this->readOptionalEntry($zip, $dir . '/_rels/' . basename($mainPart) . '.rels');
		if ($xml === null || $rels === null) {
			return [];
		}

		$targets = [];
		preg_match_all('#<Relationship\b[^>]*>#', $rels, $matches);
		foreach ($matches[0] as $relationship) {
			if (preg_match('#\bId="([^"]*)"#', $relationship, $id) && preg_match('#\bTarget="([^"]*)"#', $relationship, $target)) {
				$path = self::decode($target[1]);
				// A target is relative to the folder of the main part, unless it starts with a slash
				$targets[$id[1]] = str_starts_with($path, '/') ? ltrim($path, '/') : self::resolvePath($dir . '/' . $path);
			}
		}

		$parts = [];
		preg_match_all('#<(?:\w+:)?' . preg_quote($element, '#') . '\b[^>]*>#', $xml, $matches);
		foreach ($matches[0] as $i => $tag) {
			if (preg_match('#\b\w+:id="([^"]*)"#', $tag, $id) && isset($targets[$id[1]])) {
				$parts[$i] = $targets[$id[1]];
				if (preg_match('#\bname="([^"]*)"#', $tag, $name)) {
					$names[$i] = self::decode($name[1]);
				}
			}
		}
		return $parts;
	}

	/**
	 * Resolve "." and ".." in a path in the zip
	 */
	private static function resolvePath(string $path): string
	{
		$parts = [];
		foreach (explode('/', $path) as $part) {
			if ($part === '..') {
				array_pop($parts);
			} else if ($part !== '.' && $part !== '') {
				$parts[] = $part;
			}
		}
		return implode('/', $parts);
	}

	/**
	 * Text of Word or PowerPoint XML
	 */
	private static function ooxmlToText(string $xml): string
	{
		// Line breaks between tags are formatting. Whitespace inside <w:t> is kept.
		$xml = (string) preg_replace('#>[ \t]*\r?\n\s*<#', '><', $xml);

		$xml = self::replace($xml, [
			// Deleted text, field codes and the old copy of text boxes and other alternate content
			'#<w:delText\b[^>]*>[^<]*</w:delText>#' => '',
			'#<w:instrText\b[^>]*>[^<]*</w:instrText>#' => '',
			'#<mc:Fallback\b.*?</mc:Fallback>#s' => '',
			// Tab stop definitions, not tabs
			'#<w:tabs\b.*?</w:tabs>#s' => '',
			// The last paragraph of a cell ends the cell, not the line
			'#</w:p>(?=</w:tc>)#' => '',
			'#</w:tc>#' => "\t",
			'#</w:tr>#' => "\n",
			'#</(?:w|a):p>|<(?:w|a):p\b[^>]*/>#' => "\n",
			'#<w:tab\b[^>]*/>|<a:tab\b[^>]*/>#' => "\t",
			'#<w:br\b[^>]*/>|<w:cr\b[^>]*/>|<a:br\b[^>]*/?>#' => "\n",
		]);

		return self::decode(strip_tags($xml));
	}

	/**
	 * Text of OpenDocument XML
	 *
	 * Element names can contain a hyphen, so "(?![\w-])" ends a name instead of "\b": <table:table> is not
	 * <table:table-row>.
	 */
	private function extractOdf(ZipArchive $zip, string $format): string
	{
		$xml = $this->readZipEntry($zip, 'content.xml');

		// Line breaks between tags are formatting. Other whitespace is collapsed, like an office suite does: tabs, line
		// breaks and multiple spaces are elements.
		$xml = (string) preg_replace('#>[ \t]*\r?\n\s*<#', '><', $xml);
		$xml = (string) preg_replace('#[ \t\r\n]+#', ' ', $xml);

		$slide = 0;
		$xml = self::replace($xml, [
			// Deleted text and comments
			'#<text:tracked-changes(?![\w-]).*?</text:tracked-changes>#s' => '',
			'#<office:annotation(?![\w-]).*?</office:annotation>#s' => '',
			// The last paragraph of a cell ends the cell, not the line
			'#</text:p>\s*(?=</table:table-cell>)#' => '',
			'#</table:table-cell>|<table:covered-table-cell(?![\w-])[^>]*/>#' => "\t",
			'#</table:table-row>#' => "\n",
			'#</text:(?:p|h)>|<text:(?:p|h)(?![\w-])[^>]*/>#' => "\n",
			'#<text:tab(?![\w-])[^>]*/>#' => "\t",
			'#<text:line-break(?![\w-])[^>]*/>#' => "\n",
		]);

		$xml = (string) preg_replace_callback('#<text:s(?![\w-])[^>]*/>#', function ($match) {
			$count = preg_match('#\btext:c="(\d+)"#', $match[0], $c) ? (int) $c[1] : 1;
			return str_repeat(' ', max(1, min($count, 100)));
		}, $xml);

		// Repeated empty cells, like the rest of a row
		$xml = (string) preg_replace_callback('#<table:table-cell(?![\w-])[^>]*/>#', function ($match) {
			$count = preg_match('#\btable:number-columns-repeated="(\d+)"#', $match[0], $c) ? (int) $c[1] : 1;
			return str_repeat("\t", max(1, min($count, self::MAX_REPEATED_CELLS)));
		}, $xml);

		if ($format === 'ods') {
			$xml = (string) preg_replace_callback('#<table:table(?![\w-])[^>]*>#', function ($match) {
				$name = preg_match('#\btable:name="([^"]*)"#', $match[0], $n) ? $n[1] : '';
				return "\n## " . $name . "\n";
			}, $xml);
		} else if ($format === 'odp') {
			$xml = (string) preg_replace_callback('#<draw:page(?![\w-])[^>]*>#', function () use (&$slide) {
				$slide++;
				return "\n--- Slide " . $slide . " ---\n";
			}, $xml);
		}

		return self::decode(strip_tags($xml));
	}

	/**
	 * Replace regular expressions in order. A replacement that fails, for example because of the backtrack limit
	 * of PCRE, is skipped.
	 *
	 * @param array<string, string> $replacements Pattern => replacement
	 */
	private static function replace(string $xml, array $replacements): string
	{
		foreach ($replacements as $pattern => $replacement) {
			$result = preg_replace($pattern, $replacement, $xml);
			if ($result !== null) {
				$xml = $result;
			}
		}
		return $xml;
	}

	/**
	 * Decode the XML entities and character references. Other entities stay as they are.
	 */
	private static function decode(string $text): string
	{
		return html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
	}

	/**
	 * Remove whitespace at the end of lines and more than one empty line
	 */
	private static function normalize(string $text): string
	{
		$text = str_replace(["\r\n", "\r"], "\n", $text);
		$text = (string) preg_replace('#[ \t]+$#m', '', $text);
		$text = (string) preg_replace("#\n{3,}#", "\n\n", $text);
		return trim($text, "\n");
	}

	/**
	 * Read a PDF with pdftotext. Pages are separated with a line like "--- Page 2 ---".
	 */
	private function extractPdf(string $path): string
	{
		$pdftotext = self::findBinary('pdftotext');
		if ($pdftotext === null) {
			throw new TextExtractorException('PDF files can\'t be read, because pdftotext is not installed on the server. Install the package poppler-utils.');
		}

		$command = [$pdftotext, '-q', '-enc', 'UTF-8', $path, '-'];
		$timeout = self::findBinary('timeout');
		if ($timeout !== null) {
			array_unshift($command, $timeout, (string) self::PDF_TIMEOUT);
		}

		// An array runs the command without a shell
		$process = proc_open($command, [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);
		if (!is_resource($process)) {
			throw new TextExtractorException('pdftotext could not be started');
		}

		$output = '';
		while (!feof($pipes[1]) && strlen($output) <= $this->maxEntrySize) {
			$chunk = fread($pipes[1], 65536);
			if ($chunk === false || $chunk === '') {
				break;
			}
			$output .= $chunk;
		}
		// Closing the pipe stops pdftotext when the output is too large
		fclose($pipes[1]);
		$exitCode = proc_close($process);

		if (strlen($output) > $this->maxEntrySize) {
			$output = substr($output, 0, $this->maxEntrySize);
			$this->truncated = true;
		} else if ($exitCode === 124) {
			throw new TextExtractorException('Reading the PDF took longer than ' . self::PDF_TIMEOUT . ' seconds');
		} else if ($exitCode !== 0) {
			throw new TextExtractorException('The PDF could not be read. It may be damaged or protected with a password.');
		}

		// pdftotext ends every page with a form feed
		$pages = explode("\f", $output);
		if (end($pages) === '') {
			array_pop($pages);
		}
		if (trim(implode('', $pages)) === '') {
			return '';
		}
		$text = '';
		foreach ($pages as $i => $page) {
			$text .= '--- Page ' . ($i + 1) . " ---\n" . $page . "\n\n";
		}
		return self::normalize($text);
	}

	/**
	 * Find a program in the PATH
	 */
	private static function findBinary(string $name): ?string
	{
		$path = getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin';
		foreach (explode(PATH_SEPARATOR, $path) as $dir) {
			if ($dir !== '' && is_executable($dir . '/' . $name)) {
				return $dir . '/' . $name;
			}
		}
		return null;
	}
}
