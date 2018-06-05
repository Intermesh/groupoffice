<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Read a CSV file using Group-Office preferences
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base.util 
 */

namespace GO\Base\Csv;


class Reader{
	
	private $filename;
	public $delimiter=',';
	public $enclosure='"';
	protected $fp;
	
	public function __construct($filename) {
		if(\GO::user()){
			$this->delimiter=\GO::user()->list_separator;
			$this->enclosure=\GO::user()->text_separator;
		}
		$this->filename=$filename;
	}
	
	
	/**
	 * 
	 * Example data
	 * Example:
	 * array(
	 *	'contact' => [
	 *		'first_name'=>1,
	 *		'email'=>2,
	 *		'email2'=>3,
	 *		'home_phone'=>4,
	 *		'work_phone'=>5,
	 *		'work_fax'=>6,
	 *		'cellular'=>7,
	 *		'cellular2'=>8,
	 *		'email_allowed'=>9
	 *	],
	 *	'company' => [
	 *		'name'=>10,
	 *		'name2'=>11,
	 *		'email'=>12,
	 *		'homepage'=>13,
	 *		'phone'=>14,
	 *		'fax'=>15,
	 *		'address'=>16,
	 *		'zip'=>17,
	 *		'city'=>18,
	 *		'country'=>19,
	 *		'post_address'=>20,
	 *		'post_zip'=>21,
	 *		'post_city'=>22,
	 *		'post_country'=>23,
	 *		'bank_no'=>24,
	 *		'vat_no'=>25,
	 *		'email_allowed'=>26
	 *	],
	 *	'addresslist'=>[
	 *		'afnemerscategorie'=>27,
	 *		'hoofdafnemerscategorie'=>28
	 *	],
	 *	'import'=>[
	 *		'ImportID'=>29
	 *	]
	 * );
	 *
	 * @var array 
	 */
	private $_mapping;
	
	/**
	 * 
	 * @param type $mapping ['contact' => ['NAAM'=>'name','STRAAT'=>'address'], 'company' => ['NAAM'=>'name','STRAAT'=>'address']]
	 * Example:
	 * $mapping = array(
	 *	'contact' => [
	 *		'CONTAC'=>'first_name',
	 *		'EMAIL'=>'email',
	 *		'EMAIL2'=>'email2',
	 *		'PHOHO'=>'home_phone',
	 *		'PHOWO'=>'work_phone',
	 *		'WORKFAX'=>'work_fax',
	 *		'MOBILE'=>'cellular',
	 *		'MOBILE2'=>'cellular2',
	 *		'JANEE'=>'email_allowed'
	 *	],
	 *	'company' => [
	 *		'COMPNAM'=>'name',
	 *		'COMPNM2'=>'name2',
	 *		'COMEMA'=>'email',
	 *		'COMWEB'=>'homepage',
	 *		'COMPHO'=>'phone',
	 *		'COMFAX'=>'fax',
	 *		'COMADR'=>'address',
	 *		'COMPTT'=>'zip',
	 *		'COMWPL'=>'city',
	 *		'COMLND'=>'country',
	 *		'POSTADR'=>'post_address',
	 *		'POSTPT1'=>'post_zip',
	 *		'POSTWPL'=>'post_city',
	 *		'POSTLND'=>'post_country',
	 *		'BANK'=>'bank_no',
	 *		'CBS'=>'vat_no',
	 *		'JANEE'=>'email_allowed'
	 *	],
	 *	'addresslist'=>[
	 *		'CATEG'=>'afnemerscategorie',
	 *		'LIJST'=>'hoofdafnemerscategorie'
	 *	],
	 *	'import'=>[
	 *		'NUMMER'=>'ImportID'
	 *	]
	 * );
	 * 	 
	 */
	public function setHeaderMapping($mapping) {
		$this->setFP();
		$headers = fgetcsv($this->fp, 0, $this->delimiter, $this->enclosure);
		
		//NAAM => 2, STRAAT => 3
		$flippedHeaders = array_flip(array_map('trim',$headers));

		//we need: ['contact' => ['name'=> 1, 'address'=>3] etc/
		$this->_mapping = array();
		foreach($mapping as $model => $modelMapping) {
		
			$this->_mapping[$model] = array();
			
			//STRAAT => address
			foreach($modelMapping as $csvHeaderName => $goName) {
				$this->_mapping[$model][$goName] = $flippedHeaders[$csvHeaderName];
			}
			 		
		}
	} 
	
	public function setHeaderMappingByCSV() {
		$this->setFP();
		$headers = fgetcsv($this->fp, 0, $this->delimiter, $this->enclosure);
		
		//NAAM => 2, STRAAT => 3
		$this->_mapping['__csv'] = array_flip(array_map('trim',$headers));
		
	} 
	
	public function __destruct() {
		if(isset($this->fp)) {
			fclose($this->fp);
		}
	}
	
	/**
	 * Sets the current file handle's file pointer.
	 * @param StringHelper $mode Mode to set the file at. Default: mode read 'r'. See
	 * php's fopen documentation for possible modes.
	 */
	protected function setFP($mode='r'){
		if(!isset($this->fp))
			$this->fp = fopen($this->filename, $mode);	
		if(!is_resource($this->fp))
			throw new \Exception("Could not read CSV file");
	}
	
	/**
	 * Retrieves the contents of the next row in the CSV file. If the file's
	 * handle (i.e., file pointer) has not been set using setFP(), this will be
	 * done now, giving the file a read mode 'r'.
	 * @return Array An array of elements read from the CSV line.
	 */
	public function getRecord(){
		$this->setFP();
		
		$record = fgetcsv($this->fp, 0, $this->delimiter, $this->enclosure);
		
		if(!$record) {
			return false;
		}
		
		$record = array_map('trim', $record);
		
		if(!isset($this->_mapping)){
			return $record;
		}else
		{
			return $this->_convertRecord($record);
		}
	}
	/**
	 * Convert csv record to the mapping we've set.
	 * @param type $record
	 * @return type
	 */
	private function _convertRecord($record) {
		$converted = array();
		
		foreach($this->_mapping as $model => $mapping) {
			$converted[$model] = array();
			
			foreach($mapping as $goName => $index) {
				$converted[$model][$goName] = $record[$index];
			}
		}
		
		if(isset($converted['__csv'])){
			return $converted['__csv'];
		}  else {
			return $converted;
		}
		
		
	}
	
}
