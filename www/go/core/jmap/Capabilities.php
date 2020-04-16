<?php
namespace go\core\jmap;

use go\core\Environment;
use go\core\Singleton;

class Capabilities extends Singleton {
	
	/**
	 * The maximum file size, in bytes, that the server will accept for a 
	 * single file upload (for any purpose).
	 */
	public $maxSizeUpload;
	
	public $maxConcurrentUpload = 4;
	
	public $maxSizeRequest = 100*1000*1024; // 100MB
	
	public $maxConcurrentRequests = 4;
	
	public $maxCallInRequest = 10;
	
	public $maxObjectsInGet = 100; //broken? Infinite loop when more than this number.
	
	public $maxObjectsInSet = 1000;
	
	public function __construct() {
		$this->maxSizeUpload = Environment::get()->getMaxUploadSize();
		$this->maxSizeRequest = Environment::configToBytes(ini_get('post_max_size'));
	}
}
