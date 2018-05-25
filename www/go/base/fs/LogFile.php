<?php

namespace GO\Base\Fs;


class LogFile extends File{
	
	public function __construct($path) {
		
		parent::__construct($path);
	
		//make sure log file and parent directory exists
		if(!$this->exists())
			$this->touch (true);
	}
	
	/**
	 * Log data to file. If the data is not a string var_export will be used.
	 * 
	 * @param mixed $str
	 */
	public function log($data){
		
		if(!is_string($data))
			$data = var_export($data, true);
			
		$this->putContents($data."\n", FILE_APPEND);
	}
	
}
