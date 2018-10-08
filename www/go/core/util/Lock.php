<?php
namespace go\core\util;

use Exception;
use go\core\fs\File;
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
	
	private $unlock = false;
	
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

		$lockFile = $lockFolder->getFile($name . '.lock')->touch(true);
		
		//needs to be put in a private variable otherwise the lock is released outside the function scope
		$this->lockFp = $lockFile->open('w+');
		
		if(!$this->lockFp){
			throw new Exception("Could not create or open the file for writing.\rPlease check if the folder permissions are correct so the webserver can create and open files in it.\rPath: '" . $this->lockFile->getPath() . "'");
		}
		
		if (!flock($this->lockFp, LOCK_EX|LOCK_NB, $wouldblock)) {
			
			//unset it because otherwise __destruct will destroy the lock		
			if(is_resource($this->lockFp)) {
				fclose($this->lockFp);
			}
			
			$this->lockFp = null;
			
			if ($wouldblock) {
				// another process holds the lock
				return false;
			} else {
				throw new Exception("Could not lock controller action '" . $this->name . "'");
			}
		} 
		
		$this->unlock = true;
		
		return true;
	}
	
	/**
	 * Unlock
	 */
	public function unlock() {
		//cleanup lock file if lock() was used
		if(is_resource($this->lockFp)) {			
			flock($this->lockFp, LOCK_UN);
			fclose($this->lockFp);
			$this->lockFp = null;			
		}
	}
	
	public function __destruct() {
		if($this->unlock) {
			$this->unlock();		
		}
	}
}
