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
	public $defaultFontSize = 9;

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