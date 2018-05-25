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

namespace GO\Base\Export;


class ExportHTML extends AbstractExport {	
	
	public static $showInView = true;
	public static $name = "HTML";
	public static $useOrientation=false;
	
	private $_writeHeader;

	private function _sendHeaders() {
		header('Content-Type: text/html; charset=UTF-8');
	}

	private function _renderHead() {
		echo "<html>\n";
		echo "<head>\n<title>".$this->title."</title>\n</head>\n";
		echo "<body>\n";
		echo "<table border='1' cellspacing='0' cellpadding='0' style='border-collapse:collapse;'>\n";
	}
	
	private function _write($data) {
		if($this->header) {
			echo "<tr>\n";
			foreach($data as $column)
				echo "<th style='padding:2px; font-weight:bold;'>$column</th>";
			echo "</tr>\n";
			$this->header = false;
		} else {
			echo "<tr>\n";
			foreach($data as $column)
				echo "<td style='padding:2px;'>$column</td>";
			echo "</tr>\n";
		}
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
			if($this->humanHeaders)
				$this->_write(array_values($this->getLabels()));
			else
				$this->_write(array_keys($this->getLabels()));
		}
		
		
		while($record = $this->store->nextRecord()){
			$record = $this->prepareRecord($record);
			$this->_write($record);
		}
		
		$this->_renderFooter();
	}
}
