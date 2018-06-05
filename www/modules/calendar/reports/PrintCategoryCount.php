<?php
/**
 * Pdf instance of the printcategorycount pdf
 */

namespace GO\Calendar\Reports;


class PrintCategoryCount  extends \GO\Base\Util\Pdf {
	
	private $_cellheight = 15;
	
	public $startDate;
	public $endDate;
	
	
	public function setTitle($title){
		$this->title = $title;
	}
	
	public function setSubTitle($subtitle){
		$this->subtitle = $subtitle;
	}
	
	public function Header() {
		$this->resetColumns();
		parent::Header();
	}
	
	public function Footer() {
		$this->resetColumns();
		parent::Footer();
	}
	
	public function printBlockHeader($label,$fontsize=12){
		$this->SetTextColor(125,162,180);
		$this->SetFont($this->font,'',$fontsize);
		$this->checkPageBreak($this->_cellheight*2,'',true);
		$this->Write($this->_cellheight, $label, '', 0, 'L', true, 0, false, true, 0);
	}
	
	public function printLine($label, $value,$fontsize=10,$cellHeight=false){
		$columnLeftWidth = 170;
		$columnSeparatorWidth = 10;
		$columnRightWidth = 260;
		
		if(!$cellHeight)
			$cellHeight = $this->_cellheight;
		
		$this->SetTextColor($this->TextColor);
		$this->SetFont($this->getFontFamily(),'',$fontsize);
		
		$this->Cell($columnLeftWidth, $cellHeight, $label, false, false, 'L', false);
		$this->Cell($columnSeparatorWidth, $cellHeight, ':', false, false, 'L', false);
		$this->Cell($columnRightWidth, $cellHeight, $value, false, true, 'L', false);
	}
	
	public function printNormalLine($string,$fontsize=10,$cellHeight=false,$cellwidth=380){
		if(!$cellHeight)
			$cellHeight = $this->_cellheight;
		
		$this->checkPageBreak($cellHeight,'',true);
		$this->SetTextColor($this->TextColor);
		$this->SetFont($this->getFontFamily(),'',$fontsize);
		//$this->Cell($columnWidth, $cellHeight, $string, true, true, 'L', false);
		$this->MultiCell($cellwidth, $cellHeight, $string, false, 'L', false);
	}
	
	// Colored table
	public function htmlTable($header,$data,$totals=false) {
		$html = '<table border="0" cellpadding="3" nobr="true">';

		$html .= '<tr>';
		foreach($header as $head)
			$html .= '<th style="border-bottom:1px solid #000;"><strong>'.$head.'</strong></th>';
		$html .= '</tr>';
		
			
		foreach($data as $row){
			
			$html .= '<tr>';
			foreach($row as $r)			
				$html .= '<td>'.$r.'</td>';
			$html .= '</tr>';
		}
		
		if(!empty($totals)){
			$html .= '<tr>';
			foreach($totals as $total)			
				$html .= '<td style="border-top:1px solid #000;"><strong>'.$total.'</strong></td>';
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		$this->SetTextColor(0,0,0);
		$this->writeHTML($html,true,false,false,true);
	}

	public function render($model){
		$this->AddPage();
		$this->printBlockHeader(sprintf(\GO::t("From %s till %s", "calendar"),$model->startDate,$model->endDate));
		$this->Ln(15);		
		$this->htmlTable($model->getHeaders(),$model->getRows(),$model->getTotals());
	}
	
	public function getMargin($position){
		$margins = $this->getMargins();
		return $margins[$position];
	}
}
