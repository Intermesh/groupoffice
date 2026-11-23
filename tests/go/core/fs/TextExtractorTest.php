<?php

namespace go\core\fs;

use go\core\util\PdfRenderer;
use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * TextExtractor reads the text of office documents and PDF files without resolving entities and with limits on the
 * uncompressed size
 */
class TextExtractorTest extends TestCase
{
	private const W = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"';
	private const ODF = 'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"';

	/** @var string[] */
	private array $files = [];

	protected function tearDown(): void
	{
		foreach ($this->files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
		$this->files = [];
	}

	/**
	 * @param array<string, string> $entries Name => content
	 */
	private function createZip(array $entries, string $extension = 'zip'): string
	{
		$path = File::tempFile($extension)->getPath();
		$this->files[] = $path;
		$zip = new ZipArchive();
		$this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
		foreach ($entries as $name => $content) {
			$zip->addFromString($name, $content);
		}
		$zip->close();
		return $path;
	}

	private function createDocx(string $body): string
	{
		return $this->createZip([
			'[Content_Types].xml' => '<Types/>',
			'word/document.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
				. '<w:document ' . self::W . '><w:body>' . $body . '</w:body></w:document>'
		], 'docx');
	}

	private function createOdf(string $type, string $body): string
	{
		return $this->createZip([
			'mimetype' => 'application/vnd.oasis.opendocument.' . $type,
			'content.xml' => '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
				. '<office:document-content ' . self::ODF . '><office:body>' . $body . '</office:body></office:document-content>'
		]);
	}

	/**
	 * @param array<string, string> $slides File name in ppt/slides => text
	 * @param string[]|null $order File names in the order of presentation.xml, null for no relationships
	 */
	private function createPptx(array $slides, ?array $order): string
	{
		$entries = ['ppt/presentation.xml' => '<p:presentation xmlns:p="p" xmlns:r="r"><p:sldIdLst>'];
		$rels = '<Relationships>';
		foreach ($order ?? [] as $i => $file) {
			$entries['ppt/presentation.xml'] .= '<p:sldId id="' . (256 + $i) . '" r:id="rId' . ($i + 2) . '"/>';
			$rels .= '<Relationship Id="rId' . ($i + 2) . '" Type="slide" Target="slides/' . $file . '"/>';
		}
		$entries['ppt/presentation.xml'] .= '</p:sldIdLst></p:presentation>';
		if ($order !== null) {
			$entries['ppt/_rels/presentation.xml.rels'] = $rels . '</Relationships>';
		}
		foreach ($slides as $file => $text) {
			$entries['ppt/slides/' . $file] = '<p:sld xmlns:p="p" xmlns:a="a"><p:cSld><p:spTree><p:sp><p:txBody><a:p><a:r><a:t>'
				. $text . '</a:t></a:r></a:p></p:txBody></p:sp></p:spTree></p:cSld></p:sld>';
		}
		return $this->createZip($entries, 'pptx');
	}

	public function testDocx(): void
	{
		$path = $this->createDocx('
			<w:p><w:pPr><w:tabs><w:tab w:val="left" w:pos="720"/></w:tabs></w:pPr><w:r><w:t>Hello</w:t></w:r><w:r><w:tab/><w:t xml:space="preserve">world &amp; „friends“</w:t></w:r></w:p>
			<w:p><w:r><w:t>Line one</w:t><w:br/><w:t>Line two</w:t></w:r><w:del><w:r><w:delText>deleted</w:delText></w:r></w:del><w:r><w:instrText> MERGEFIELD x </w:instrText></w:r></w:p>
			<w:tbl><w:tr><w:tc><w:p><w:r><w:t>A1</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>B1</w:t></w:r></w:p></w:tc></w:tr></w:tbl>
			<w:p><w:r><mc:AlternateContent><mc:Choice Requires="wps"><w:t>Box</w:t></mc:Choice><mc:Fallback><w:t>Box old</w:t></mc:Fallback></mc:AlternateContent></w:r></w:p>
			<w:p/>
			<w:p><w:r><w:t>&lt;gotpl if="contact:mentor"&gt;Mentor: {contact:mentor}&lt;/gotpl&gt;</w:t></w:r></w:p>
		');

		$this->assertEquals('docx', TextExtractor::detectFormat($path));
		$extractor = new TextExtractor();
		$this->assertEquals(
			"Hello\tworld & „friends“\nLine one\nLine two\nA1\tB1\nBox\n\n<gotpl if=\"contact:mentor\">Mentor: {contact:mentor}</gotpl>",
			$extractor->extract($path)
		);
		$this->assertFalse($extractor->truncated);
	}

	public function testEntitiesAreNotResolved(): void
	{
		$path = $this->createZip([
			'word/document.xml' => '<?xml version="1.0"?><!DOCTYPE w:document [<!ENTITY xxe SYSTEM "file:///etc/passwd"><!ENTITY lol "lol">]>'
				. '<w:document ' . self::W . '><w:body><w:p><w:r><w:t>A&xxe;B&lol;C&#65;</w:t></w:r></w:p></w:body></w:document>'
		]);

		$text = (new TextExtractor())->extract($path);
		$this->assertStringNotContainsString('root:', $text);
		$this->assertStringContainsString('A&xxe;B&lol;CA', $text);
	}

	public function testPptxSlidesInPresentationOrder(): void
	{
		$slides = ['slide1.xml' => 'One', 'slide2.xml' => 'Two', 'slide10.xml' => 'Ten'];

		$path = $this->createPptx($slides, ['slide2.xml', 'slide10.xml', 'slide1.xml']);
		$this->assertEquals('pptx', TextExtractor::detectFormat($path));
		$this->assertEquals("--- Slide 1 ---\nTwo\n\n--- Slide 2 ---\nTen\n\n--- Slide 3 ---\nOne", (new TextExtractor())->extract($path));

		// Without relationships the files are taken in their natural order
		$path = $this->createPptx($slides, null);
		$this->assertEquals("--- Slide 1 ---\nOne\n\n--- Slide 2 ---\nTwo\n\n--- Slide 3 ---\nTen", (new TextExtractor())->extract($path));
	}

	public function testXlsx(): void
	{
		$path = $this->createZip([
			'xl/workbook.xml' => '<workbook xmlns:r="r"><sheets><sheet name="Names &amp; ages" sheetId="1" r:id="rId1"/></sheets></workbook>',
			'xl/_rels/workbook.xml.rels' => '<Relationships><Relationship Id="rId1" Type="worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
			'xl/sharedStrings.xml' => '<sst><si><t>Name</t></si><si><r><t>Jo</t></r><r><t xml:space="preserve">hn</t></r><rPh><t>ジョン</t></rPh></si><si><t>Age</t></si></sst>',
			'xl/worksheets/sheet1.xml' => '<worksheet><sheetData>'
				. '<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>2</v></c></row>'
				. '<row r="2"><c r="A2" t="s"><v>1</v></c><c r="C2" s="1"><f>40+2</f><v>42</v></c></row>'
				. '<row r="3"/>'
				. '<row r="4"><c r="A4" t="inlineStr"><is><t>x</t></is></c><c r="B4" t="b"><v>1</v></c><c r="C4"/></row>'
				. '</sheetData></worksheet>',
		], 'xlsx');

		$this->assertEquals('xlsx', TextExtractor::detectFormat($path));
		$this->assertEquals("## Names & ages\nName\tAge\nJohn\t\t42\n\nx\tTRUE", (new TextExtractor())->extract($path));
	}

	public function testOdt(): void
	{
		$path = $this->createOdf('text', '<office:text>
			<text:h text:outline-level="1">Title</text:h>
			<text:p>Hello<text:tab/>world<text:s text:c="2"/>end</text:p>
			<text:p>One<text:line-break/>Two <text:span>three</text:span></text:p>
			<text:tracked-changes><text:changed-region><text:deletion><text:p>gone</text:p></text:deletion></text:changed-region></text:tracked-changes>
			<table:table table:name="T"><table:table-row><table:table-cell><text:p>A1</text:p></table:table-cell><table:table-cell><text:p>B1</text:p></table:table-cell></table:table-row></table:table>
		</office:text>');

		$this->assertEquals('odt', TextExtractor::detectFormat($path));
		$this->assertEquals("Title\nHello\tworld  end\nOne\nTwo three\nA1\tB1", (new TextExtractor())->extract($path));
	}

	public function testOds(): void
	{
		$path = $this->createOdf('spreadsheet', '<office:spreadsheet><table:table table:name="Sheet1">'
			. '<table:table-row><table:table-cell><text:p>a</text:p></table:table-cell><table:table-cell table:number-columns-repeated="2"/><table:table-cell><text:p>b</text:p></table:table-cell></table:table-row>'
			. '<table:table-row table:number-rows-repeated="1000"><table:table-cell table:number-columns-repeated="16384"/></table:table-row>'
			. '</table:table></office:spreadsheet>');

		$this->assertEquals('ods', TextExtractor::detectFormat($path));
		$this->assertEquals("## Sheet1\na\t\t\tb", (new TextExtractor())->extract($path));
	}

	public function testOdp(): void
	{
		$path = $this->createOdf('presentation', '<office:presentation>'
			. '<draw:page draw:name="page1"><draw:frame><draw:text-box><text:p>First</text:p></draw:text-box></draw:frame></draw:page>'
			. '<draw:page draw:name="page2"><draw:frame><draw:text-box><text:p>Second</text:p></draw:text-box></draw:frame></draw:page>'
			. '</office:presentation>');

		$this->assertEquals('odp', TextExtractor::detectFormat($path));
		$this->assertEquals("--- Slide 1 ---\nFirst\n\n--- Slide 2 ---\nSecond", (new TextExtractor())->extract($path));
	}

	public function testEntryIsReadUpToTheLimit(): void
	{
		// Compresses to a few hundred bytes
		$path = $this->createDocx('<w:p><w:r><w:t>' . str_repeat('a', 100000) . '</w:t></w:r></w:p>');

		$extractor = new TextExtractor(1000);
		$text = $extractor->extract($path);
		$this->assertTrue($extractor->truncated);
		$this->assertLessThanOrEqual(1000, strlen($text));
		$this->assertStringStartsWith('aaa', $text);

		$xml = $extractor->readEntry($path, 'word/document.xml');
		$this->assertTrue($extractor->truncated);
		$this->assertEquals(1000, strlen($xml));
	}

	public function testTotalLimit(): void
	{
		$text = str_repeat('a', 600);
		$path = $this->createPptx(['slide1.xml' => $text, 'slide2.xml' => $text, 'slide3.xml' => $text], ['slide1.xml', 'slide2.xml', 'slide3.xml']);

		$extractor = new TextExtractor(1000, 2000);
		$result = $extractor->extract($path);
		$this->assertTrue($extractor->truncated);
		// The third slide is read only in part
		$this->assertLessThan(1800, substr_count($result, 'a'));
		$this->assertGreaterThan(1200, substr_count($result, 'a'));
	}

	public function testReadEntry(): void
	{
		$path = $this->createDocx('<w:p><w:r><w:t>“quoted”</w:t></w:r></w:p>');

		$extractor = new TextExtractor();
		$xml = $extractor->readEntry($path, 'word/document.xml');
		$this->assertStringContainsString('<w:t>“quoted”</w:t>', $xml);
		$this->assertFalse($extractor->truncated);

		$this->assertNull($extractor->readEntry($path, 'word/missing.xml'));
		$this->assertEquals(['[Content_Types].xml', 'word/document.xml'], TextExtractor::listEntries($path));
	}

	public function testReadEntryOfOtherFileFails(): void
	{
		$path = File::tempFile('txt')->getPath();
		$this->files[] = $path;
		file_put_contents($path, 'plain text');

		$this->assertNull(TextExtractor::detectFormat($path));
		$this->assertEquals([], TextExtractor::listEntries($path));
		$this->expectException(TextExtractorException::class);
		(new TextExtractor())->readEntry($path, 'content.xml');
	}

	public function testOtherZipIsNotSupported(): void
	{
		$path = $this->createZip(['readme.txt' => 'hello']);
		$this->assertEquals('zip', TextExtractor::detectFormat($path));
		$this->expectException(TextExtractorException::class);
		(new TextExtractor())->extract($path);
	}

	public function testPdf(): void
	{
		$pdf = new PdfRenderer();
		$pdf->AddPage();
		$pdf->Write(10, 'Hello PDF');
		$pdf->AddPage();
		$pdf->Write(10, 'Second page');
		$path = File::tempFile('pdf')->getPath();
		$this->files[] = $path;
		file_put_contents($path, $pdf->Output('', 'S'));

		$this->assertEquals('pdf', TextExtractor::detectFormat($path));

		$installed = trim((string) shell_exec('command -v pdftotext')) !== '';
		if (!$installed) {
			$this->expectException(TextExtractorException::class);
			$this->expectExceptionMessage('poppler-utils');
		}
		$text = (new TextExtractor())->extract($path);
		// TCPDF adds a "Powered by TCPDF" line to a page
		$this->assertMatchesRegularExpression('#^--- Page 1 ---\n.*Hello PDF.*\n\n--- Page 2 ---\n.*Second page$#s', $text);
	}
}
