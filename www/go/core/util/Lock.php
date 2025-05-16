<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace go\core\util;

use Exception;
use go\core\ErrorHandler;
use go\core\fs\File;
use go\core\jmap\Request;
use LogicException;
use function GO;

/**
 * Create a lock to prevent the same action to run twice by multiple users
 */
class Lock {
	private static $locks = [];

	/**
	 * @var null|\SysvSemaphore|false
	 */
	private $sem;
	/**
	 * @var bool
	 */
	private $blocking;

	private $startTime;

	/**
	 * Timeout in seconds
	 * @var int
	 */
	public $timeout = 10;

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
	 * @var ?resource
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

		if(!isset($this->startTime)) {
			$this->startTime = microtime(true);
		}

		if(function_exists('sem_get') && empty(go()->getConfig()['lockWithFlock'])) {
			//performs better but is not always available
			$locked = $this->lockWithSem();
		} else
		{
			$locked = $this->lockWithFlock();
		}

		if($locked) {
			$this->lockedByMe = true;

			// removed this to reduce IO
			// for debugging lock problem. Store request infor user ID and PID number
			//$this->getLockFile()->putContents($this->getRequestInfo());

			//reset start time to take off the waiting time. We want to use it for measuring the lock time now.
			$this->startTime = microtime(true);
			return true;
		} else {

			if(!$this->blocking) {
				return false;
			}

			if($this->timeout > 0 && $this->timeTaken() > $this->timeout) {
				throw new Exception("Lock timeout with name: " . $this->name);
//				$info = $this->getLockFile()->getContents();
//				throw new Exception("Waiting for lock (" . $this->getRequestInfo() .") for longer than " . $this->timeout."s. Lock is held by (" . $info . ")");
			}

			go()->debug("Locked");
			//sleep for 100 milliseconds
			usleep(100000);
			return $this->lock();
		}
	}


	private function getRequestInfo(): string
	{
		$userId = go()->getAuthState() ? (go()->getAuthState()->getUserId() ? go()->getAuthState()->getUserId() : '-') : '-';
		return getmypid() . ' ' . $userId . ' ' . go()->getDebugger()->getRequestId();
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
			$acquired = $this->sem && sem_acquire($this->sem, true);
		} catch(Exception $e) {
			ErrorHandler::logException($e, "Failed to acquire lock for " . $this->name);
			//identifier might be removed by other process
			$acquired = false;
		}

		if(!$acquired) {
			$this->sem = null;
		}
		return $acquired;
	}


	private function getLockFile() {
		$lockFolder = GO()
			->getTmpFolder()
			->getFolder('locks');

		$name = File::stripInvalidChars($this->name);

		return $lockFolder->getFile($name . '.lock')->touch(true);
	}

	/**
	 * Lock with flock() function
	 *
	 * @throws Exception
	 */
	private function lockWithFlock() : bool {

		$lockFile = $this->getLockFile();

		//needs to be put in a private variable otherwise the lock is released outside the function scope
		$this->lockFp = $lockFile->open('r');

		if(!$this->lockFp){
			throw new Exception("Could not create or open the file for writing.\rPlease check if the folder permissions are correct so the webserver can create and open files in it.\rPath: '" . $lockFile->getPath() . "'");
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
				//happens on apache restart: https://stackoverflow.com/questions/36084158/what-is-the-reason-for-flock-to-return-false
				throw new Exception("Could not obtain excusive lock with name '" . $this->name . "'");
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
			sem_release($this->sem);
			sem_remove($this->sem);
			$this->sem = null;
		} else 	if(is_resource($this->lockFp)) {
			flock($this->lockFp, LOCK_UN);
			fclose($this->lockFp);
			$this->lockFp = null;			
		}

		$this->lockedByMe = false;

		//Warn about long lock times in error log
		$lockTime = $this->timeTaken();
		if($this->timeout > 0 && $this->blocking && $lockTime > 1) {
			$userId = (go()->getAuthState() ? go()->getAuthState()->getUserId() : '-');
			ErrorHandler::log("Lock " . $this->name . " by " .$userId ." in request ". go()->getDebugger()->getRequestId() . ' with pid '.getmypid().' took '.$lockTime.'s');
		}

		$this->startTime = null;
	}

	private function timeTaken() {
		return microtime(true) - $this->startTime;
	}
	
	public function __destruct() {
		$this->unlock();
		unset(self::$locks[$this->name]);
	}
}
