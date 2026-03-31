<?php
namespace go\core\util;

use Exception;
use go\core\fs\Folder;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF_FONTS;

require_once(__DIR__ . '/tcpdf_config.php');

/**
 * PDF renderer
 */
class PdfRenderer extends Fpdi {

	/**
	 * The default font to use in the PDF
	 * @var string
	 */
	public $defaultFont = 'dejavusans';

	/**
	 * Default font size in pt
	 *
	 * @var float
	 */
	public $defaultFontSize = 10;

//	/**
//	 * Line height
//	 * @var double
//	 */
//	public $lh = 4;

	/**
	 * Constructor
	 *
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or Portrait (default)</li><li>L or Landscape</li><li>'' (empty string) for automatic orientation</li></ul>
	 * @param $unit (string) User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul><br />A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.
	 * @param $size (mixed) The size used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 */
	public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {

		parent::__construct($orientation, $unit, $size);

		$this->SetFont($this->defaultFont, "", $this->defaultFontSize);

		$this->setAuthor(go()->getSettings()->title);

		//Set normal font
		$this->normal();

		$this->setHtmlVSpace([
			'p' => [0 => ['h' => 0, 'n' => 0.1], 1 => ['h' => 0, 'n' => 0.1]],
			'div' => [0 => ['h' => 0, 'n' => 0.1], 1 => ['h' => 0, 'n' => 0.1]],
			'ol' => [0 => ['h' => 0, 'n' => 0.1], 1 => ['h' => 0, 'n' => 0.1]],
			'ul' => [0 => ['h' => 0, 'n' => 0.1], 1 => ['h' => 0, 'n' => 0.1]],
		]);

		$this->setCellHeightRatio(1.25);
		$this->SetCellPadding(0);
	}


	public static function sanitize(string $text): string {
//		// Fix encoding if not valid UTF-8
//		if (!mb_check_encoding($text, 'UTF-8')) {
//			$text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
//		}
//
//		// Normalize unicode to NFC form
//		if (class_exists('Normalizer')) {
//			$text = Normalizer::normalize($text, Normalizer::FORM_C);
//		}
//
//		// Replace fancy punctuation and special characters
//		$text = strtr($text, [
//			"\u{2018}" => "'",  // left single quote
//			"\u{2019}" => "'",  // right single quote
//			"\u{201C}" => '"',  // left double quote
//			"\u{201D}" => '"',  // right double quote
//			"\u{2013}" => '-',  // en dash
//			"\u{2014}" => '--', // em dash
//			"\u{2026}" => '...', // ellipsis
//			"\u{00A0}" => ' ',  // non-breaking space
//			"\u{200B}" => '',   // zero-width space
//			"\u{FEFF}" => '',   // BOM / zero-width no-break space
//		]);
//
//		// Remove control characters (keep \n \r \t)
//		$text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '?1?', $text);
//
//		// Remove 4-byte characters outside BMP (emoji, rare CJK, etc.)
//		$text = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '?2?', $text);
//
//		// Remove Private Use Area characters (U+E000–U+F8FF)
//		$text = preg_replace('/[\x{E000}-\x{F8FF}]/u', '?3?', $text);
//
//		// Remove Specials block (U+FFF0–U+FFFF) including replacement char U+FFFD
//		$text = preg_replace('/[\x{FFF0}-\x{FFFF}]/u', '?4?', $text);

		// Remove zero-width and directional control characters
		$text = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}]/u', '?5?', $text);

		// Remove combining diacritical marks DejaVu may lack glyphs for
//		$text = preg_replace('/[\x{0300}-\x{036F}]/u', '?6?', $text);

		return $text;
	}

	/**
	 * Get all available fonts
	 *
	 * @return array eg. [['name' => 'Arial', 'family' => 'arial', 'core' =>
	 * @throws Exception
	 */
	public static function getFonts() : array {
		$folder = new Folder(K_PATH_FONTS);
		$files = $folder->find([
			'regex' => '/.*\.php/'
		]);

		$fonts = [];
		foreach($files as $file) {
			$name = null;
			require($file);
			$fonts[$file->getNameWithoutExtension()] = $name;
		}

		return $fonts;
	}

	public static function addTTFFont(string $ttfPath) {
		// convert TTF font to TCPDF format and store it on the fonts folder
		return TCPDF_FONTS::addTTFfont($ttfPath, 'TrueTypeUnicode', '', 32);

	}

	/**
	 * Set font to bold
	 *
	 * @return $this
	 */
	public function bold() {
		$this->SetFont($this->FontFamily, 'B', $this->FontSizePt);
		return $this;
	}

	/**
	 * Set font to italic
	 *
	 * @return $this
	 */
	public function italic() {
		$this->SetFont($this->FontFamily, 'I', $this->FontSizePt);
		return $this;
	}

	/**
	 * Change font to default
	 *
	 * @return $this
	 */
	public function normal() {
		$this->SetFont($this->FontFamily, '', $this->FontSizePt);
		return $this;
	}

	/**
	 * Change font size
	 *
	 * @param float $pt
	 * @return $this
	 */
	public function size($pt = null) {

		if(!isset($pt)) {
			$pt = $this->defaultFontSize;
		}

		$this->SetFontSize($pt);
		return $this;
	}

	/**
	 * Draw horizontal ruler
	 *
	 * @return $this
	 */
	public function hr() {

		$this->Line($this->lMargin, $this->getY(), $this->w-$this->rMargin, $this->getY());

		return $this;
	}

	public function Header() {

	}

	/**
	 * Renders the content of the PDF
	 *
	 * Use Output() to write the PDF to string or file:
	 *
	 * $pdf->render()->Output($file->getPath(), "F");
	 *
	 * @return self
	 * @throws Exception
	 */
	public function render() {
		return $this;
	}

}