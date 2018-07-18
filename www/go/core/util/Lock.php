<?php
namespace go\core\util;

use Exception;
use go\core\fs\File;
use go\core\http\Exception as Exception2;
use function GO;

/**
 * Create a lock to prevent the same action to run twice by multiple users
 */
class Lock {
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	private $name;
	
	/**
	 * The file pinter for the lock method
	 * 
	 * @var resource 
	 */
	private $lockFp;
	
	/**
	 * The lock file name.
	 * Stored to cleanup after the script ends
	 */
	private $lockFile;
	
	/**
	 * Lock an action
	 * 
	 * Call this to make sure it can only be executed by one user at the same time.
	 * Useful for the system upgrade action for example
	 * 
	 * @throws Exception
	 * @return boolean returns true if the lock was successful and false if already locked
	 */
	public function lock() {

		$lockFolder = GO()
						->getDataFolder()
						->getFolder('locks');
		
		$name = File::stripInvalidChars($this->name);

		$this->lockFile = $lockFolder->getFile($name . '.lock');
		
		//needs to be put in a private variable otherwise the lock is released outside the function scope
		$this->lockFp = $this->lockFile->open('w+');
		
		if (!flock($this->lockFp, LOCK_EX|LOCK_NB, $wouldblock)) {
			
			//unset it because otherwise __destruct will destroy the lock
			unset($this->lockFile, $this->lockFp);
			
			if ($wouldblock) {
				// another process holds the lock
				return false;
			} else {
				throw new Exception("Could not lock controller action '" . $this->name . "'");
			}
		} 
		
		return true;
	}
	
	/**
	 * Unlock
	 */
	public function unlock() {
		//cleanup lock file if lock() was used
		if(isset($this->lockFile)) {
			fclose($this->lockFp);
			if(file_exists($this->lockFile)) {
				unlink($this->lockFile);			
			}
		}
	}
	
	public function __destruct() {
		$this->unlock();		
	}
}
