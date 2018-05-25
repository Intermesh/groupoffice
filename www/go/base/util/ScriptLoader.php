<?php

namespace GO\Base\Util;


class ScriptLoader{
	
	private $_cacheFile;
	
	
	public function __construct(){
		
		$f = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'cache');
		$f->create();
		
		$this->_cacheFile = \GO::config()->file_storage_path.'cache/script-';
		
		if(\GO::user()){
			$this->_cacheFile .= \GO::user()->username;
		}else
		{
			$this->_cacheFile .= "loggedoff";
		}
		$this->_cacheFile .= '.js';
	}
	
	public function clear(){
		unlink($this->_cacheFile);
	}
	
	public function addScriptFile($path){
		file_put_contents($this->_cacheFile, file_get_contents($path),FILE_APPEND);
	}
	
	public function addScriptCode($code){
		file_put_contents($this->_cacheFile, $code,FILE_APPEND);
	}
	
	public function output(){
		readfile($this->_cacheFile);
	}
	
	public function getCacheFilePath(){
		return $this->_cacheFile;
	}
	
	
}
