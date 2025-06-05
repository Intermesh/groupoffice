<?php

namespace go\core;

use ErrorException;
use Exception;
use go\core\util\DateTime;
use Throwable;

/**
 * Error handler class
 * 
  * All PHP errors will be converted into ErrorExceptions. If they are not caught
 * by the developers code then they will be handled by {@see exceptionHandler()}
 * It will render the error and log it to the system log using error_log 
 * regardless of the php.ini settings.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ErrorHandler {

	public function __construct()
	{
		set_error_handler([$this, 'errorHandler'], E_ALL);
		register_shutdown_function([$this, 'shutdown']);
		set_exception_handler([$this, 'exceptionHandler']);
	}

	/**
	 * Called when PHP exits.
	 */
	public function shutdown()
	{
		$error = error_get_last();
		if ($error) {
			//Log only fatal errors because other errors should have been logged by the normal error handler
			if (in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
				$this->exceptionHandler(new ErrorException($error['message'],0,$error['type'],$error['file'], $error['line']));
			}
		}
	}

	/**
	 * Log exception to PHP logging system and debug the exception in GO
	 *
	 * @param Throwable $e
	 * @param string|null $context Extra information about where the exception occurred
	 * @return string The string that was logged. This contains sensitive information like the server path so use only for debugging!
	 */
	public static function logException(Throwable $e, string|null $context = null): string
	{
		$cls = get_class($e);
		
		$errorString = $cls . " in " . $e->getFile() ." at line ". $e->getLine().': '.$e->getMessage();

		if(isset($context)) {
			$errorString .= ', ' . $context;
		}

		if(!Environment::get()->isCli()) {
			error_log($errorString, 0);
		}

		if(!go()->getDebugger()->enabled) {
			return $errorString;
		}
		
		App::get()->getDebugger()->error($errorString);

		$previous = $e->getPrevious();
		if($previous) {
			App::get()->getDebugger()->error("Previous: " . $previous->getMessage());
		}

		$lines = explode("\n", $e->getTraceAsString());
		foreach($lines as $line) {
			App::get()->getDebugger()->error($line);
		}
		
		return $errorString;
	}

	/**
	 * Send a messag
	 */
	public static function log($str): bool
	{
		go()->error($str);
		return error_log($str, 0);
	}

	/**
	 * Log backtrace to main error log for debugging purposes.
	 *
	 * @return void
	 */
	public static function logBacktrace() {
		$str = (new Exception())->getTraceAsString();

		$lines = explode("\n", $str);

		foreach($lines as $line) {
			error_log($line, 0);
		}
	}

	/**
	 * Handles uncaught exceptions
	 *
	 * @param Throwable $e
	 */
	public function exceptionHandler(Throwable $e) {
		go()->debug("ErrorHandler::exceptionHandler() called with " . get_class($e));

		self::logException($e);
		
		if(!headers_sent()) {
			if($e instanceof http\Exception) {
				http_response_code($e->code);				
			} else{
				http_response_code(500);
			}
			header('Content-Type: text/plain');
		}
		
		echo "Uncaught exception: " . $e->getMessage() . " at ".date(DateTime::FORMAT_API)."\n\n";
			
		if(go()->getDebugger()->enabled) {

			echo $e->getTraceAsString() ."\n";

			echo "\n\nDebug dump: \n\n";			
			App::get()->getDebugger()->printEntries();
		}
	}

  /**
   * Custom error handler that logs to our own error log
   *
   * @param int $errno
   * @param string $errstr
   * @param string $errfile
   * @param int $errline
   * @return void
   * @throws ErrorException
   */
	public static function errorHandler(int $errno, string $errstr, string $errfile, int $errline) {

		// do not use debug here because it will lead to a loop when error is thrown inside App::getConfig()
		//go()->debug("ErrorHandler:errorHandler called $errno");

		// check if error should be reported according to PHP settings
		if (!(error_reporting() & $errno)) {
		  return;
		}

		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}
