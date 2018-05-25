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
 * HTML Output stream.
 * 
 * @version $Id: ExportHTML.php 7607 2011-08-04 13:41:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.export
 */

namespace GO\Base\Storeexport;


class ExportHTML extends AbstractExport {	
	
	public static $showInView = true;
	public static $name = "HTML";
	public static $useOrientation=false;
	

	private function _sendHeaders() {
		header('Content-Type: text/html; charset=UTF-8');
	}

	private function _renderHead() {
		echo "<html>\n";
		echo "<head>\n<title>".$this->title."</title>\n<style>body{font:12px Arial;}</style>\n</head>\n";
		echo "<body>\n";
		echo "<table border='1' cellspacing='0' cellpadding='0' style='border-collapse:collapse;'>\n";
	}
	
	private function _write($data) {
		
		
		
		if($this->header) {
			echo "<tr>\n";
			foreach($data as $column=>$header){
				$align = in_array($column, $this->totalizeColumns) ? 'right' : 'left';
				
				echo "<th style='padding:4px; font-weight:bold;text-align:$align;'>$header</th>";
			}
			echo "</tr>\n";
			$this->header = false;
		} else {
			echo "<tr>\n";
			foreach($data as $column=>$value){
				$align = in_array($column, $this->totalizeColumns) ? 'right' : 'left';
				echo "<td style='padding:4px;text-align:$align;'>$value</td>";
			}
			echo "</tr>\n";
		}
	}	
	
	private function _writeTotals($data) {
		
		echo "<tr>\n";
		foreach($data as $column)
			echo "<td style='padding:4px;border:0;border-top:2px solid black;text-align:right'>$column</td>";
		echo "</tr>\n";
		
	}	

	private function _renderFooter() {
		echo "</table>\n";
		echo "</body>\n";
		echo "</html>";
	}
	
	public function output() {
		$this->_sendHeaders();
		$this->_renderHead();
		
//		$labels = $this->getLabels();
//		if($this->header) {
//			$this->header = true;
//			$this->_write(array_values($labels));
//		}
		if($this->header){
			$this->header = true;
			$this->_write($this->getLabels());

		}
		
		
		while($record = $this->store->nextRecord()){
			$record = $this->prepareRecord($record);
			$this->_write($record);
		}
		
		if(($totals = $this->getTotals())){
			$this->_writeTotals($totals);
		}
		
		$this->_renderFooter();
	}
}
