<?php
namespace go\modules\community\addressbook\model;

use go\core\TemplateParser;
use setasign\Fpdi;

class Labels extends \TCPDF {

	private $templateParser;

	public $cols = 2;
	public $rows = 8;

	public $labelTopMargin = 10;
	public $labelRightMargin = 10;
	public $labelBottomMargin = 10;
	public $labelLeftMargin = 10;

	public $pageTopMargin = 10;
	public $pageRightMargin = 20;
	public $pageBottomMargin = 10;
	public $pageLeftMargin = 20;

	private $currentColumn = 0;
	private $currentRow = 0;
	private $labelWidth;
	private $labelHeight;

	public function __construct($unit = 'mm', $format = 'A4')
	{
		parent::__construct('P', $unit, $format, true, 'UTF-8', false, false);

		$this->SetCreator('Group-Office ' . go()->getVersion());
		$this->SetCreator(go()->getAuthState()->getUser(['displayName'])->displayName);
		$this->SetSubject("Labels");
		$this->SetFont('dejavusans', '', 10);
		$this->setCellMargins(0, 0, 0, 0);
		$this->SetPrintFooter(false);
		$this->SetPrintHeader(false);

		$this->templateParser = new TemplateParser();
	}

	public function Header()
	{
	}

	public function Footer()
	{
	}

	public function render($contactIds, $tpl, $filename = 'Contact Labels.pdf') {


		$this->SetMargins($this->pageLeftMargin,$this->pageTopMargin,$this->pageRightMargin);
		$this->SetAutoPageBreak(true, $this->pageBottomMargin);

		$this->SetAbsXY(0,0);

		$this->setCellPaddings($this->labelLeftMargin, $this->labelTopMargin, $this->labelRightMargin, $this->labelBottomMargin);

		$this->AddPage();

		$this->labelWidth = ($this->getPageWidth() - $this->pageLeftMargin - $this->pageRightMargin) / $this->cols;
		$this->labelHeight = ($this->getPageHeight() - $this->pageTopMargin - $this->pageBottomMargin) / $this->rows;

		$contacts = Contact::find()->where('id', 'IN', $contactIds);

		foreach($contacts as $contact) {
			$this->addLabel($contact, $tpl);
		}

		$tmpFile = go()->getTmpFolder()->getFile($filename);
		$this->Output($tmpFile->getPath(), 'F');

		return $tmpFile;
	}

	private function addLabel(Contact $contact, $tpl) {
		$this->templateParser->addModel('contact', $contact);
		$txt = $this->templateParser->parse($tpl);
		$this->MultiCell($this->labelWidth, $this->labelHeight, $txt, 0, 'L', 0, 0, '', '', true,0,false,true,$this->labelHeight,'T', false);

		$this->nextLabelPosition();

	}

	private function nextLabelPosition() {
		$this->currentColumn++;
		if($this->currentColumn == $this->cols) {
			$this->currentColumn = 0;
			$this->currentRow++;
			if($this->currentRow == $this->rows) {
				$this->AddPage();
				$this->currentRow = 0;
			}
		}

		$x = ($this->labelWidth * $this->currentColumn) + $this->pageLeftMargin;
		$y = ($this->labelHeight * $this->currentRow) + $this->pageTopMargin;

		$this->SetAbsXY($x, $y);
	}
}
