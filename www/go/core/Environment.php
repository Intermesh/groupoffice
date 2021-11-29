<?php

namespace go\core;

/**
 * Server information class.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Environment extends Singleton {

	/**
	 * Check if this is a windows server
	 * 
	 * @return boolean
	 */
	public function isWindows() {
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
	
	/**
	 * Check if we are executed with the Command Line Interface
	 * 
	 * @return boolean
	 */
	public function isCli() {
		return PHP_SAPI === 'cli';
	}

	/**
	 * Check if we are executed within the cron environment
	 *
	 * @return boolean
	 */
	public function isCron() {
		return basename($_SERVER['PHP_SELF']) == 'cron.php';
	}

	/**
	 * Get PHP memory limit in bytes
	 * 
	 * @return int
	 */
	public function getMemoryLimit() {
		return self::configToBytes(ini_get('memory_limit'));
	}

	public function setMemoryLimit($limit) {
		ini_set('memory_limit', $limit);
	}
	
	/**
	 * Get the maximum size of a file upload
	 * 
	 * To increase increase in php.ini:
	 *
	 * 1. post_max_size
	 * 2. upload_max_filesize
	 * 
	 * The smallest value will apply here.
	 * 
	 * @return int
	 */
	public function getMaxUploadSize() {
		return min(self::configToBytes(ini_get('post_max_size')), self::configToBytes(ini_get('upload_max_filesize')));
	}

	/**
	 * Converts shorthand memory notation value to bytes
	 * From http://php.net/manual/en/function.ini-get.php
	 *
	 * @param string $val Memory size shorthand notation string
	 */
	public static function configToBytes($val) {
		$val = trim($val);
		$last = strtolower(substr($val,-1));
		$val = substr($val, 0, -1);
		switch ($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'P':  
        $val *= 1024;  
			case 'T':  
				$val *= 1024;
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return (int) $val;
	}
	
	private $installFolder;
	
	/**
	 * Get the folder where Group-Office is installed
	 * 
	 * @return \go\core\fs\Folder
	 */
	public function getInstallFolder() {
		
		if(!isset($this->installFolder)) {
			$this->installFolder = new fs\Folder($this->getInstallPath());
		}
		
		return $this->installFolder;
	}
	
	/**
	 * Get install path without trailing slash
	 * eg /usr/share/groupoffice
	 * 
	 * @return string
	 */
	public function getInstallPath() {
		return dirname(dirname(__DIR__));
	}

	/**
	 * Check if the Ioncube loader has been installed.
	 *
	 * @return bool
	 */
	public function hasIoncube() {
		return extension_loaded('ionCube Loader');
	}

}
