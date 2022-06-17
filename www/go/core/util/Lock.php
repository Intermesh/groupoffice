<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace go\core\util;

use Exception;
use go\core\fs\File;
use LogicException;
use function GO;

/**
 * Create a lock to prevent the same action to run twice by multiple users
 */
class Lock {
	private static $locks = [];

	/**
	 * @var resource
	 */
	private $sem;
	/**
	 * @var bool
	 */
	private $blocking;

	public function __construct(string $name, bool $blocking = true) {
		$this->name = $name;
		$this->blocking = $blocking;

		if(isset(self::$locks[$name])) {
			throw new LogicException("Lock '" . $name . "' already exists!");
		}

		self::$locks[$name] = true;
	}

	/**
	 * Check if lock exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function exists(string $name) : bool {
		return isset(self::$locks[$name]);
	}
	
	private $name;
	
	/**
	 * The file pinter for the lock method
	 * 
	 * @var resource 
	 */
	private $lockFp;

	private $lockedByMe = false;
	
	/**
	 * Lock an action
	 * 
	 * Call this to make sure it can only be executed by one user at the same time.
	 * Useful for the system upgrade action for example
	 * 
	 * @throws Exception
	 * @return boolean returns true if the lock was successful and false if already locked
	 */
	public function lock() : bool {

		if($this->lockedByMe) {
			throw new LogicException("Lock '" . $this->name . "' already locked by you!");
		}

		if(function_exists('sem_get')) {
			//performs better but is not always available
			$locked = $this->lockWithSem();
		} else
		{
			$locked = $this->lockWithFlock();
		}

		if($locked) {
			$this->lockedByMe = true;
		}

		return $locked;
	}

	/**
	 * Lock with Semaphore extension
	 *
	 * @return bool
	 */
	private function lockWithSem() : bool {

		// prepend db name for multi instance
		try {
			$this->sem = sem_get((int)hexdec(substr(md5(go()->getConfig()['db_name'] . $this->name), 24)));
			$acquired = sem_acquire($this->sem, !$this->blocking);
		} catch(Exception $e) {
			//identifier might be removed by other process
			$acquired = false;
			//echo $e->getMessage() ."\n";
		}

		if(!$this->blocking) {
			return $acquired;
		} else {
			if(!$acquired) {
				sleep(1);
				return $this->lockWithSem();
			} else {
				return true;
			}
		}
	}

	/**
	 * Lock with flock() function
	 *
	 * @throws Exception
	 */
	private function lockWithFlock() : bool {

		$lockFolder = GO()
			->getDataFolder()
			->getFolder('locks');

		$name = File::stripInvalidChars($this->name);

		$lockFile = $lockFolder->getFile($name . '.lock')->touch(true);

		//needs to be put in a private variable otherwise the lock is released outside the function scope
		$this->lockFp = $lockFile->open('w+');

		if(!$this->lockFp){
			throw new Exception("Could not create or open the file for writing.\rPlease check if the folder permissions are correct so the webserver can create and open files in it.\rPath: '" . $lockFile->getPath() . "'");
		}

		if (!flock($this->lockFp, $this->blocking ? LOCK_EX : LOCK_EX|LOCK_NB, $wouldblock)) {

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

		return true;
	}
	
	/**
	 * Unlock
	 */
	public function unlock() {
		//cleanup lock file if lock() was used

		if(!$this->lockedByMe) {
			return;
		}

		if(isset($this->sem)) {
			sem_remove($this->sem);
			$this->sem = null;
		} else 	if(is_resource($this->lockFp)) {
			flock($this->lockFp, LOCK_UN);
			fclose($this->lockFp);
			$this->lockFp = null;			
		}

		$this->lockedByMe = false;
	}
	
	public function __destruct() {
		$this->unlock();
		unset(self::$locks[$this->name]);
	}
}
