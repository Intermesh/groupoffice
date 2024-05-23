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

namespace go\core\csv;


use Exception;

class Reader {
	
	private string $filename;
	public string $delimiter=',';
	public string $enclosure='"';
	protected mixed $fp;
	
	public function __construct(string $filename) {
		if(go()->getAuthState() && ($user = go()->getAuthState()->getUser())){
			$this->delimiter = $user->listSeparator;
			$this->enclosure = $user->textSeparator;
		}
		$this->filename = $filename;
	}
	
	
	/**
	 * Set mapping
	 *
	 * Example:
	 * [
	 *		'first_name',
	 *		'email',
	 *		'email2',
	 *		'home_phone',
	 *		'work_phone'
	 *	]
	 *
	 * @var array 
	 */
	public array $mapping;

	
	public function setHeaderMappingByCSV(): void
	{
		$this->setFP();
		$headers = fgetcsv($this->fp, 0, $this->delimiter, $this->enclosure);
		
		//NAAM => 2, STRAAT => 3
		$this->mapping = array_map('trim',$headers);
		
	}

	private function mapRecord(array $record): array
	{
		$mapped = [];

		foreach($record as $index => $value) {
			$mapped[$this->mapping[$index] ?? $index] = $value;
		}

		return $mapped;
	}
	
	public function __destruct() {
		if(isset($this->fp)) {
			fclose($this->fp);
		}
	}

	/**
	 * Sets the current file handle's file pointer.
	 * @param string $mode Mode to set the file at. Default: mode read 'r'. See
	 * php's fopen documentation for possible modes.
	 * @throws Exception
	 */
	protected function setFP(string $mode = 'r'){
		if(!isset($this->fp))
			$this->fp = fopen($this->filename, $mode);	
		if(!is_resource($this->fp))
			throw new Exception("Could not read CSV file");
	}

	/**
	 * Retrieves the contents of the next row in the CSV file. If the file's
	 * handle (i.e., file pointer) has not been set using setFP(), this will be
	 * done now, giving the file a read mode 'r'.
	 * @return array|false An array of elements read from the CSV line.
	 * @throws Exception
	 */
	public function getRecord() : array | false{
		$this->setFP();
		
		$record = fgetcsv($this->fp, 0, $this->delimiter, $this->enclosure);
		
		if(!$record) {
			return false;
		}
		
		$record = array_map('trim', $record);
		
		if(!isset($this->mapping)){
			return $record;
		}else
		{
			return $this->mapRecord($record);
		}
	}
		
		

	
}
