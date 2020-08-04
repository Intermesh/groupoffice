<?php
namespace go\core\util;

use Exception;
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
	}

	/**
	 * Set in constructor when the PDF has a stationary PDF
	 *
	 * @var int[]
	 */
	private $tplIdx;

	public function Header() {

		//use stationary PDF
		if(isset($this->tplIdx) && count($this->tplIdx)) {

			//use every page of the template. If the invoice has more pages use the last page.
			$tplIdx = isset($this->tplIdx[$this->page]) ? $this->tplIdx[$this->page] : $this->tplIdx[count($this->tplIdx)];
			$this->useTemplate($tplIdx);
		}
	}

	/**
	 * This method is used to render the page footer.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */
	public function Footer() {
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));

		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($this->original_rMargin);
			$this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
		} else {
			$this->SetX($this->original_lMargin);
			$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 0, 0, 'R');
		}
	}

	public function render() {

		$this->AddPage();

		$currentX = $this->getX();
		$currentY = $this->getY();


		foreach($this->template->blocks as $block) {

			$this->normal();

			if(isset($block->x)) {
				$this->setX($block->x);
			}

			if(isset($block->y)) {
				$this->setY($block->y);
			}

			if(!isset($block->width)) {
				$block->width = $this->w-$this->lMargin - $this->rMargin;
			}

			if($this->previewMode) {
				$block->type = 'text';
			}
			$method = 'renderBlock'.$block->type;

			if(!method_exists($this, $method)) {
				if($this->previewMode) {
					$block->type = 'text';
					$method = 'renderBlock'.$block->type;
				} else {
					throw new Exception("Invalid block tag ".$block->type);
				}
			}

			$this->setCellPaddings(0,0,0,0);
			$this->normal();

			$this->$method($block);

			if(isset($block->x)) {
				$this->setX($currentX);
			}else
			{
				$currentX = $this->getX();
			}

			if(isset($block->y)) {
				$this->setY($currentY);
			}else
			{
				$currentY = $this->getY();
			}
		}
		return parent::render();
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
