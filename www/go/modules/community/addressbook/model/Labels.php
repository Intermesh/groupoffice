<?php
namespace go\modules\community\addressbook\model;

use go\core\TemplateParser;
use setasign\Fpdi;

class Labels extends \TCPDF {

	private $templateParser;

	public $cols = 2;
	public $rows = 8;

	public $cellTopMargin = 10;
	public $cellRightMargin = 10;
	public $cellBottomMargin = 10;
	public $cellLeftMargin = 10;

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

		$this->SetMargins(0,0,0);
		$this->SetAutoPageBreak(true, 0);


		// set font
		$this->SetFont('dejavusans', '', 10);

// add a page
		$this->AddPage();

// set cell padding
		$this->setCellMargins(0, 0, 0, 0);

		$this->SetPrintFooter(false);
		$this->SetPrintHeader(false);
		$this->SetAbsXY(0,0);


// set cell margins

		$this->templateParser = new TemplateParser();


	}

	public function Header()
	{
	}

	public function Footer()
	{
	}

	public function render($contactIds, $tpl, $filename = 'Contact Labels.pdf') {

		$this->setCellPaddings($this->cellLeftMargin, $this->cellTopMargin, $this->cellRightMargin, $this->cellBottomMargin);

		$this->labelWidth = ($this->getPageWidth() / $this->cols);
		$this->labelHeight = ($this->getPageHeight() / $this->rows);

		$contacts = Contact::find()->where('id', 'IN', $contactIds);

		foreach($contacts as $contact) {
			$this->addLabel($contact, $tpl);
		}

		$tmpFile = go()->getTmpFolder()->getFile($filename);
		$this->Output($tmpFile->getPath(), 'F');

		return $tmpFile;
	}

	private function addLabel(Contact $contact, $tpl) {
		$this->templateParser->addModel('contact', $contact->toTemplate());
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

		$x = $this->labelWidth * $this->currentColumn;
		$y = $this->labelHeight * $this->currentRow;

		$this->SetAbsXY($x, $y);
	}
}
