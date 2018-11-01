<?php

namespace GO\Base;

class Environment {
	
	public function isCli(){
		
		$cli = PHP_SAPI=='cli';
		if(!$cli && PHP_SAPI=='cgi-fcgi' && isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['SERVER_ADDR']))
			return $_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'];
		else
			return $cli;
		return false;
		
	}
	
	
}
