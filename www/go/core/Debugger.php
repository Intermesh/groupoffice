<?php
namespace go\core;

use go\core\data\ArrayableInterface;
use go\core\data\Model;

/**
 * Debugger class. All entries are stored and the view can render them eventually.
 * The JSON view returns them all.
 * 
 * The client can enable by sending an HTTP header X-Debug=1 (Use CTRL + F7 in webclient)
 * 
 * Example:
 * 
 * ````````````````````````````````````````````````````````````````````````````
 * \go\core\App::get()->debug($mixed);
 * ````````````````````````````````````````````````````````````````````````````
 * 
 * or:
 * 
 * ````````````````````````````````````````````````````````````````````````````
 * \go\core\App::get()->getDebugger()->debugCalledFrom();
 * ````````````````````````````````````````````````````````````````````````````
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Debugger {
	
	const SECTION_INIT = 'init';
	
	const SECTION_ROUTER = 'router';
	
	const SECTION_CONTROLLER = 'controller';
	
	const SECTION_VIEW = 'view';
	
	const LEVEL_LOG = 'log';
	
	const LEVEL_WARN = 'warn';
	
	const LEVEL_INFO = 'info';
	
	const LEVEL_ERROR = 'error';

	/**
	 * Sets the debugger on or off
	 * @var boolean
	 */
	public $enabled = false;

	/**
	 * When set all visible debug messaged are written to this file
	 * @var string Full path on FS
	 */
	public $logPath;

	private $logFp;
	/**
	 * The debug entries as strings
	 * @var array
	 */
	private $entries = [];
	
	public function __construct() {
		try {
			if(!empty(go()->getConfig()['core']['general']['debug']) && (!isset($_REQUEST['r']) || $_REQUEST['r']!='core/debug')) {
				$this->enable();
			}

		} catch (\go\core\exception\ConfigurationException $e) {
			//GO is not configured / installed yet.
			$this->enabled = true;
		}
	}

	public function enable() {
		$this->enabled = true;
		if(go()->getConfig()['core']['general']['debugLog']) {
			$logFile = go()->getDataFolder()->getFile('log/debug.log');
			if($logFile->isWritable()) {
				if(!$logFile->exists()) {
					$logFile->touch(true);
				}
				$this->logPath = $logFile->getPath();
				$this->logFp = $logFile->open('a+');
			}
		}
	}

	protected $currentGroup;
	protected $groupStartTime;

	public function group($name) {		
		if(!$this->enabled) {
			return;
		}
		$this->entries[] = ['groupCollapsed', $name];
		$this->currentGroup = &$this->entries[count($this->entries)-1][1];
		$this->groupStartTime = $this->getTimeStamp();

		$this->writeLog('start', $name . ' '. date('Y-m-d H:i:s'));
	}

	public function groupEnd(){
		if(!$this->enabled) {
			return;
		}
		$time = (int) ($this->getTimeStamp() - $this->groupStartTime);
		$this->currentGroup .= ', time: '.$time.'ms';

		$this->currentGroup .= ", Peak memory usage: " . number_format(memory_get_peak_usage() / (1024 * 1024), 2) . 'MB';			

		$this->entries[] = ['groupEnd', null];

		$this->writeLog('end', $this->currentGroup);
	}

	/**
	 * Get time in seconds with microseconds
	 * 
	 * @return float seconds
	 */
	public function getMicroTime() {
		if(!$this->enabled) {
			return;
		}
		// list ($usec, $sec) = explode(" ", microtime());
		// return ((float) $usec + (float) $sec);
		return microtime(true);
	}	
	
	public function warn($mixed, $traceBackSteps = 0) {
		$this->internalLog($mixed, self::LEVEL_WARN, $traceBackSteps);
	}
	
	public function error($mixed, $traceBackSteps = 0) {
		$this->internalLog($mixed, self::LEVEL_ERROR, $traceBackSteps);
	}
	
	public function info($mixed, $traceBackSteps = 0) {
		$this->internalLog($mixed, self::LEVEL_INFO, $traceBackSteps);
	}
	
	public function debug($mixed, $traceBackSteps = 0) {
		$this->log($mixed, $traceBackSteps);
	}
	
	public function log($mixed, $traceBackSteps = 0) {
		$this->internalLog($mixed, self::LEVEL_LOG, $traceBackSteps);
	}
	

	/**
	 * Add a debug entry. Objects will be converted to strings with var_export();
	 * 
	 * You can also provide a closure function so code will only be executed when
	 * debugging is enabled.
	 *
	 * @todo if for some reason an error occurs here then an infinite loop is created
	 * @param callable|string|object $mixed
	 * @param string $level The type of message. Types can be arbitrary and can be enabled and disabled for output. {@see self::$enabledTypes}
	 */
	private function internalLog($mixed, $level = self::LEVEL_LOG, $traceBackSteps = 0) {

		if(!$this->enabled) {
			return;
		}		
		
		if($mixed instanceof \Closure) {
			$mixed = call_user_func($mixed);
		}elseif(is_object($mixed) && method_exists($mixed, '__toString')) {
			$mixed = (string) $mixed;
		}
		// elseif (!is_scalar($mixed)) {
		// 	$mixed = print_r($mixed, true);
		// }
		
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7 + $traceBackSteps);
		
//		var_dump($bt);
		$lastCaller = null;
		$caller = array_shift($bt);
		//can be called with \go\core\App::get()->debug(). We need to go one step back (no class for closure)
		while(isset($caller['class']) && ($caller['function'] == 'debug' || $caller['function'] == 'warn' || $caller['function'] == 'error' || $caller['function'] == 'info' || $caller['class'] == self::class)) {
			$lastCaller = $caller;
			$caller = array_shift($bt);
		}
		
		$count = count($bt);
		
		$traceBackSteps = min([$count, $traceBackSteps]);
		
		while($traceBackSteps > 0) {			
			$lastCaller = $caller;
			$caller = array_shift($bt);
			$traceBackSteps--;			
		}
		
		if(empty($caller['class'])) {			
			$caller['class'] = $lastCaller['class'] ?? "none";
		}
		
		if(!isset($lastCaller['line'])) {
			$lastCaller['line'] = '[unknown line]';
		}
		
		//$entry = "[" . $this->getTimeStamp() . "][" . $caller['class'] . ":".$lastCaller['line']."] " . $mixed;

		$this->writeLog($level, $mixed, $caller['class'], $lastCaller['line']);
		
		$this->entries[] = [$level, $mixed, $caller['class'], $lastCaller['line']];
		
	}

	protected function writeLog($level, $mixed, $cls = null, $lineNo = null) {

		if(is_array($mixed) || $mixed instanceof ArrayableInterface) {
			$print = print_r(Model::convertValueToArray($mixed), true);
		}elseif (!is_scalar($mixed)) {
			$print = print_r($mixed, true);
		} else if(is_bool($mixed)) {
			$print = $mixed ? "TRUE" : "FALSE";
		}	else {
			$print = $mixed;
		}
		$line = '[' . $level . ']';
		
		if(isset($cls)) {
			$line .= '[' . $cls .':'. $lineNo.']';
		}

		$line .=  ' ';

		if(strstr($print, "\n")) {
			$print = "\n        " . str_replace("\n", "\n        ", $print);
		}
		
		$line .=   $print . "\n";

		if($level == 'start') {
			$line = "\n" . $line;
		}

		// if(go()->getEnvironment()->isCli()) {
		// 	echo $line;
		// }

		if(is_resource($this->logFp)) {
			fputs($this->logFp, $line);
		}
	}

	/**
	 * Add a message that notes the time since the request started in milliseconds
	 * 
	 * @param string $message
	 */
	public function debugTiming($message) {
		if(!$this->enabled) {
			return;
		}
		$this->debug((int) ($this->getTimeStamp()) . "ms ". $message);
	}

	/**
	 * Get the ellapsed time since the start of the request in milliseconds
	 * 
	 * @return float milliseconds
	 */
	public function getTimeStamp() {
		if(!$this->enabled) {
			return;
		}
		return ($this->getMicroTime() * 1000) - ($_SERVER["REQUEST_TIME_FLOAT"] * 1000);
	}

	public function debugCalledFrom($limit = 10) {
		if(!$this->enabled) {
			return;
		}

		$this->debug("START BACKTRACE");
		$trace = debug_backtrace();

		$count = count($trace);

		$limit++;
		if ($limit > $count) {
			$limit = $count;
		}

		for ($i = 1; $i < $limit; $i++) {
			$call = $trace[$i];

			if (!isset($call["file"])) {
				$call["file"] = 'unknown';
			}
			if (!isset($call["function"])) {
				$call["function"] = 'unknown';
			}

			if (!isset($call["line"])) {
				$call["line"] = 'unknown';
			}

			$this->debug("Function: " . $call["function"] . " called in file " . $call["file"] . " on line " . $call["line"]);
		}
		$this->debug("END BACKTRACE");
	}
	
	/**
	 * Get the debugger entries
	 * 
	 * @return array
	 */
	public function getEntries() {
		return $this->entries;
	}
	
	/**
	 * Print all entries
	 */
	public function printEntries() {
		echo implode("\n", array_map(function($e){return is_scalar($e[1]) ? $e[1] : print_r($e[1]);}, $this->entries));
	}
	
	/**
	 * Returns the type of a given variable.
	 * 
	 * @param mixed $var
	 * @return string
	 */
	public static function getType($var) {
		if(is_object($var)) {
			return get_class($var);
		}else
		{
			return gettype($var);
		}
	}

}
