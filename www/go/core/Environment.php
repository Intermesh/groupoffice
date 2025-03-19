<?php

namespace go\core;

use go\core\fs\File;
use go\core\fs\Folder;
use go\core\util\ClassFinder;

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
	public function isWindows(): bool
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
	
	/**
	 * Check if we are executed with the Command Line Interface
	 * 
	 * @return boolean
	 */
	public function isCli(): bool
	{
		return PHP_SAPI === 'cli';
	}

	/**
	 * Check if we are executed within the cron environment
	 *
	 * @return boolean
	 */
	public function isCron(): bool
	{
		return basename($_SERVER['PHP_SELF']) == 'cron.php';
	}

	/**
	 * Get PHP memory limit in bytes
	 * 
	 * @return int
	 */
	public function getMemoryLimit(): int
	{
		return self::configToBytes(ini_get('memory_limit'));
	}

	/**
	 * Set the memory limit. It will only set if it's higher than the current limit.
	 *
	 * @param string $limit
	 * @return bool|string
	 */
	public function setMemoryLimit(string $limit): bool|string
	{
		$current = ini_get('memory_limit');
		$currentBytes = self::configToBytes($current);
		$newBytes = self::configToBytes($limit);
		if($newBytes > $currentBytes) {
			return ini_set('memory_limit', $limit);
		} else {
			return $current;
		}
	}

	/**
	 * Set the max execution time. It will only set if it's higher than the current time
	 * @param int $time In seconds
	 */
	public function setMaxExecutionTime(int $time): bool|string
	{
		$current = ini_get("max_execution_time");
		if($current != 0 && ($time == 0 || $time > $current)){
			return ini_set('max_execution_time', $time);
		} else {
			return $current;
		}
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
	public function getMaxUploadSize(): int
	{
		return min(self::configToBytes(ini_get('post_max_size')), self::configToBytes(ini_get('upload_max_filesize')));
	}

	/**
	 * Converts shorthand memory notation value to bytes
	 * From http://php.net/manual/en/function.ini-get.php
	 *
	 * @param string $val Memory size shorthand notation string
	 * @noinspection PhpMissingBreakStatementInspection
	 */
	public static function configToBytes(string $val): int
	{
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
	 * @return Folder
	 */
	public function getInstallFolder(): Folder
	{
		
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
	public function getInstallPath(): string
	{
		return dirname(__DIR__, 2);
	}

	/**
	 * Check if the Ioncube loader has been installed.
	 *
	 * @return bool
	 */
	public function hasIoncube(): bool
	{
		$version = phpversion("sourceGuardian");

		return ($version && version_compare($version, '14.0.0', '>=')) || !self::sourceIsEncoded();

	}

	private static function sourceIsEncoded() : bool {

		$isEncoded = go()->getCache()->get('source-is-encoded');

		if($isEncoded === null) {
			$isEncoded = ClassFinder::fileIsEncoded(new File(dirname(__DIR__) . '/modules/business/license/model/License.php'));
			go()->getCache()->set('source-is-encoded', $isEncoded);
		}

		return $isEncoded;


	}
}
