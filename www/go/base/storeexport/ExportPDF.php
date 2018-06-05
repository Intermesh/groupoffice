<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * PDF Output stream.
 * 
 * @version $Id: ExportPDF.php 7607 2011-08-04 13:41:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.export
 */

namespace GO\Base\Storeexport;


class ExportPDF extends AbstractExport {

	public static $showInView = true;
	public static $name = "PDF";
	public static $useOrientation=true;

//	private function _write($data) {
//		$html = '';
//		if($this->header) {
//			$html .= '<tr nobr="true">';
//			foreach($data as $column)
//				$html .=  "<th>$column</th>";
//			$html .=  "</tr>";
//			$this->header = false;
//		} else {
//			$html .=  '<tr nobr="true">';
//			foreach($data as $column)
//				$html .=  "<td>$column</td>";
//			$html .=  "</tr>";
//		}
//		return $html;
//	}	
//	
	private function _write($data) {
		$html = '';
		if($this->header) {
			$html .= '<tr nobr="true" style="background-color:#f1f1f1">';
			foreach($data as $column=>$header){
								
				$align = in_array($column, $this->totalizeColumns) ? 'right' : 'left';
				
				$html .= '<th align="'.$align.'"><b>'.$header.'</b></th>';
			}
			$html .= "</tr>\n";
			$this->header = false;
		} else {
			$html .= '<tr nobr="true">';
			foreach($data as $column=>$value){
				$align = in_array($column, $this->totalizeColumns) ? 'right' : 'left';
				$html .= '<td align="'.$align.'">'.$value.'</td>';
			}
			$html .= "</tr>\n";
		}
		
		return $html;
	}
	
	private function _writeTotals($data) {
		$html = '';
		
		$html .= '<tr nobr="true"><td colspan="'.count($data).'"></td>';
		
		$html .= "</tr>\n";
		
		$html .= '<tr nobr="true">';
		foreach($data as $column)
			$html .= '<td align="right">'.$column.'</td>';
		$html .= "</tr>\n";
		
		return $html;
		
	}
	
	public function output(){
		
		$html = '<table border="1" cellspacing="0" cellpadding="2">';
	
		if($this->header)
		{
			$html .= $this->_write($this->getLabels());		
		}
		
		
		while($record = $this->store->nextRecord()){
			$record = $this->prepareRecord($record);
			$html .= $this->_write($record);
		}
		
		if(($totals = $this->getTotals())){
			$html .=$this->_writeTotals($totals);
		}
		
		$html .= "</table>";

		$this->_createPDF($html);
	}
	
	private function _createPDF($html) {
		// The standard orientation is Portret
		$orientation='P';
		
		if(!empty($this->orientation)){
			$orientation = $this->orientation;
		}
		
		$pdf = new \GO\Base\Util\Pdf($orientation, $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false);

		$pdf->SetTitle($this->title);
		$pdf->SetSubject($this->title);
		$pdf->SetAuthor($this->title);
		$pdf->SetCreator($this->title);
		$pdf->SetKeywords($this->title);
		

		$pdf->AddPage();
		$pdf->writeHTML($html, true, false, true, false, '');
		
		$pdf->Output($this->title.'.pdf');
	}

}
