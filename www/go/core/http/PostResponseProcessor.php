<?php
namespace go\core\http;

use go\core\ErrorHandler;
use go\core\Singleton;
use Throwable;

/**
 * Runs tasks after the response to the client has been sent and the connection is closed.
 * This way the client can continue fast and the PHP process can do some more work.
 */
class PostResponseProcessor extends Singleton {

	private array $tasks = [];
	public function addTask(callable $callable) {
		\go\core\jmap\Response::get()->continueAfterOutput = true;
		$this->tasks[] = $callable;
	}

	public function __destruct()
	{
		if (empty($this->tasks)) {
			return;
		}

		foreach ($this->tasks as $task) {
			try {
				call_user_func($task);
			}catch (Throwable $e) {
				ErrorHandler::logException($e, "In post response task");
			}
		}
	}
}
