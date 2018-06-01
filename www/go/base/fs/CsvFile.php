<?php
/**
 * Copyright Intermesh BV
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * Reads and writes CSV files.
 * 
 * @package GO.base.fs
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */

/**
 * Reads OR writes CSV files.
 * 
 * @package GO.base.fs
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Base\Fs;


class CsvFile extends File{
	public $delimiter;
	
	public $enclosure;
	
	/**
	 * @var \GO\Base\Csv\Writer or \GO\Base\Csv\Reader, see _setCSV
	 */
	private $_csv;
	
	private function _setCSV($mode){
		
		if(!isset($this->delimiter)){
			$this->delimiter = \GO::user() ? \GO::user()->list_separator : ',';
		}
		
		if(!isset($this->enclosure)){
			$this->enclosure = \GO::user() ? \GO::user()->text_separator : '"';
		}
		
		if(!isset($this->_csv)){
			$this->_csv = new \GO\Base\Csv\Writer($this->path());			
			$this->_csv->delimiter=$this->delimiter;
			$this->_csv->enclosure=$this->enclosure;
		}
	}
		
	/**
	 * Reads the elements of the next row of a CSV file. After first
	 * use of this method, $this->putRecord() cannot be used.
	 * @return Array The row of the CSV file as an array.
	 */
	public function getRecord(){
		$this->_setCSV('r');
		return $this->_csv->getRecord();
	}

	/**
	 * Writes the elements of an array to the next row of a CSV file. After first
	 * use of this method, $this->getRecord() MUST NOT be used.
	 * @param Array $fields The elements of this array will be written into a line
	 * of the current CSV file.
	 * @return int The length of the written string, or false on failure.
	 */
	public function putRecord($fields){
		$this->_setCSV('a+');		
		return $this->_csv->putRecord($fields);
	}
	
}
