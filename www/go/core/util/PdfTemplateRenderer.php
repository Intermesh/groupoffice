<?php
namespace go\core\util;

use Exception;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\model\PdfTemplate;
use go\core\TemplateParser;
use go\core\util\PdfRenderer;
use go\core\model\PdfBlock;

/**
 * Renders a PDF from template
 *
 * @example
 * `````````````````````````````````````````````````````````````````````````````
 *
 * $template = Pdf::findByPk(1);
 *
 * $models = ['foo' => $record];
 *
 * $pdf = new PdfRenderer($template, $models);
 *
 * GO()->getResponse()->setHeader('Content-Type', 'application/pdf');
GO()->getResponse()->setHeader('Content-Disposition', 'inline; filename="' . $template->name . '.pdf"');
GO()->getResponse()->setHeader('Content-Transfer-Encoding', 'binary');

 * echo $pdf->render();
 *
 * `````````````````````````````````````````````````````````````````````````````
 */
class PdfTemplateRenderer extends PdfRenderer {

	/**
	 *
	 * @var PdfTemplate
	 */
	protected $template;

	/**
	 *
	 * @var TemplateParser;
	 */
	protected $parser;

	public $previewMode = false;

	public $lh = 4;

	/**
	 * Constructor
	 *
	 * @param PdfTemplate $template
	 * @param array $templateModels Key value array that will be used to parse templates. eg. ['invoice' => $invoice] {@see VariableParser::addModel()}
	 */
	public function __construct(PdfTemplate $template, $templateModels = []) {

		$this->template = $template;

		$orientation = $this->template->landscape ? 'L' : 'P';

		$this->parser = new TemplateParser();
		$this->parser->addModel('template', $this->template);
		foreach($templateModels as $name => $model) {
			$this->parser->addModel($name, $model);
		}

		parent::__construct($orientation, $this->template->measureUnit, $this->template->pageSize);

		$this->SetTopMargin($this->template->marginTop);
		$this->SetLeftMargin($this->template->marginLeft);
		$this->SetRightMargin($this->template->marginRight);
		$this->SetAutoPageBreak(true, $this->template->marginBottom);

		// Set the source PDF file
		$stationary = $template->getStationary();
		if($stationary) {
			$numberOfPages = $this->setSourceFile($stationary->getFile()->getPath());
			// Import the first page of the template PDF
			for($i = 1; $i <= $numberOfPages; $i++) {
				$this->tplIdx[$i] = $this->importPage($i);
			}
		}

		$this->allowLocalFiles = true;

		$logo = $this->template->getLogo();
		if($logo) {
			$this->parser->addModel("logo", "file://" . $logo->getFile()->getPath());
		}

	}

	public function getParser(): TemplateParser
	{
		return $this->parser;
	}

	public function getTemplate(): PdfTemplate
	{
		return $this->template;
	}

	/**
	 * <tcpdf method="logo"></tcpdf>
	 * @return void
	 */
	protected function logo() {
		$blob = $this->template->getLogo();
		if(!$blob) {
			return;
		}

		$img = $this->Image($blob->getFile()->getPath());

		$b = $this->getImageBuffer($img);
	}

	/**
	 * Set in constructor when the PDF has a stationary PDF
	 *
	 * @var int[]
	 */
	private $tplIdx;

	public function Header() {

		$this->SetFont($this->defaultFont, "", $this->defaultFontSize);

		//Set normal font
		$this->normal();

		//use stationary PDF
		if(isset($this->tplIdx) && count($this->tplIdx)) {

			//use every page of the template. If the invoice has more pages use the last page.
			$tplIdx = isset($this->tplIdx[$this->page]) ? $this->tplIdx[$this->page] : $this->tplIdx[count($this->tplIdx)];
			$this->useTemplate($tplIdx);
		}


		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}

		$this->parser->addModel('pageNumber', $this->getPage());
		//Print page number
		if ($this->getRTL()) {
			$this->parser->addModel('pageNumberWithTotal', $pagenumtxt);
		} else {
			$this->parser->addModel('pageNumberWithTotal', $this->getAliasRightShift().$pagenumtxt);

		}


		if($this->template->header) {
			$this->setX($this->template->headerX);
			$this->setY($this->template->headerY);
			$data = $this->previewMode ? $this->template->header : $this->parser->parse($this->template->header);
			$this->writeHTML($data);


			$this->setX(0);
			$this->setY($this->template->marginTop);
		}
	}

	/**
	 * This method is used to render the page footer.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */
	public function Footer() {

		$this->SetFont($this->defaultFont, "", $this->defaultFontSize);

		//Set normal font
		$this->normal();

		if($this->template->footer) {

			$this->setX($this->template->footerX);
			$this->setY($this->template->footerY);
			$data = $this->previewMode ? $this->template->footer : $this->parser->parse($this->template->footer);
			$this->writeHTML( $data);
		}
	}

	/**
	 * Renders the content of the PDF
	 *
	 * Use Output() to write the PDF to string or file:
	 *
	 * $pdf->render()->Output($file->getPath(), "F");
	 *
	 * @return PdfTemplateRenderer
	 * @throws Exception
	 */
	public function render() {

		try {

			$oldLang = go()->getLanguage()->setLanguage($this->template->language);

			$this->AddPage();

			$currentX = $this->getX();
			$currentY = $this->getY();


			foreach ($this->template->blocks as $block) {

				$this->normal();

				if (isset($block->x)) {
					$this->setX($block->x);
				}

				if (isset($block->y)) {
					$this->setY($block->y);
				}

				if (!isset($block->width)) {
					$block->width = $this->w - $this->lMargin - $this->rMargin;
				}

				if ($this->previewMode) {
					$block->type = 'text';
				}
				$method = 'renderBlock' . $block->type;

				if (!method_exists($this, $method)) {
					if ($this->previewMode) {
						$block->type = 'text';
						$method = 'renderBlock' . $block->type;
					} else {
						throw new Exception("Invalid block tag " . $block->type);
					}
				}

				$this->setCellPaddings(0, 0, 0, 0);
				$this->normal();

				$this->$method($block);

				if (isset($block->x)) {
					$this->setX($currentX);
				} else {
					$currentX = $this->getX();
				}

				if (isset($block->y)) {
					$this->setY($currentY);
				} else {
					$currentY = $this->getY();
				}
			}
		}
		catch(\Exception $e) {
			$this->MultiCell($this->w - $this->lMargin - $this->rMargin,  14,  ErrorHandler::logException($e), 0, "L");
			if(go()->getDebugger()->enabled) {
				$this->MultiCell($this->w - $this->lMargin - $this->rMargin, 14, $e->getTraceAsString(), 0, "L");
			}
		}
		$ret =  parent::render();

		go()->getLanguage()->setLanguage($oldLang);

		return $ret;
	}


	private function renderBlockText(PdfBlock $block) {

		if(!isset($block->height)) {
			$block->height = $this->lh;
		}

		$data = $this->previewMode ? $block->content : $this->parser->parse($block->content);

		$this->MultiCell(
			$block->width,
			$block->height,
			$data,
			$this->previewMode ? 1 : 0, //border
			$block->align,
			false, //fill
			1,  //Line break
			isset($block->x) ? $block->x : '',
			isset($block->y) ? $block->y : ''
		);

		$this->setLastH($this->lh);

	}
	private function renderBlockHtml(PdfBlock $block) {

		if(isset($block->height)) {
			$y = $block->height + $this->getY();
		}

		$data = $this->previewMode ? $block->content : $this->parser->parse($block->content);

		try {
			$this->writeHTMLCell(
				$block->width,
				$block->height,
				isset($block->x) ? $block->x : '',
				isset($block->y) ? $block->y : '',
				$data,
				0,//border
				1 //ln
			);
		} catch(\Exception $e) {
			//ignore error in html
		}

		if(isset($y)) {
			$this->setY($y);
		}
	}
}
