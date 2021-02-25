<?php
namespace go\core;


/**
 * Debugger class. All entries are stored and the view can render them eventually.
 * The JSON view returns them all.
 * 
 * The client can enable by sending an HTTP header X-Debug=1
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
	
	const TYPE_GENERAL = 'general';
	
	const TYPE_SQL = 'sql';
	
	private $section = self::SECTION_INIT;

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
	
	/**
	 * List of enabled debug sections.
	 * 
	 * This controls the output of the debugger so you don't get too much debug 
	 * info. In most cases developers need just self::SECTION_CONTROLLER
	 * 
	 * By default there are:
	 * 
	 * `````````````````````````````````````````````````````````````````````
	 * [self::SECTION_INIT, self::SECTION_ROUTER, self::SECTION_CONTROLLER, self::SECTION_VIEW];
	 * `````````````````````````````````````````````````````````````````````
	 * 
	 * But developers can use any arbitrary string as section
	 * 
	 * @var array 
	 */
	public $enabledSections = [self::SECTION_INIT, self::SECTION_ROUTER, self::SECTION_CONTROLLER];
	
	/**
	 * List of enabled debug types.
	 * 
	 * This controls the output of the debugger so you don't get too much debug 
	 * info.
	 * 
	 * By default there are:
	 * 
	 * `````````````````````````````````````````````````````````````````````
	 * [self::TYPE_GENERAL, self::TYPE_SQL];
	 * `````````````````````````````````````````````````````````````````````
	 * 
	 * But developers can use any arbitrary string as type
	 * @var type 
	 */
	public $enabledTypes = [self::TYPE_GENERAL, self::TYPE_SQL];

	/**
	 * The debug entries as strings
	 * @var array
	 */
	private $entries = [];
	
	public function __construct() {
		try {
			$this->enabled = !empty(GO()->getConfig()['general']['debug']) && (!isset($_REQUEST['r']) || $_REQUEST['r']!='core/debug');
			if($this->enabled) {
				$this->logPath = GO()->getDataFolder()->getFile('log/debug.log')->getPath();
			}
		} catch (\go\core\exception\ConfigurationException $e) {
			//GO is not configured / installed yet.
			$this->enabled = true;
		}
	}

	/**
	 * Get time in milliseconds
	 * 
	 * @return float Milliseconds
	 */
	public function getMicroTime() {
		list ($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}
	
	/**
	 * Change the section the debugger is in
	 * 
	 * {@see self::$enabledSections}
	 * 
	 * @param string $section
	 */
	public function setSection($section) {
		$this->section = $section;
		$this->debug("Start section '" . $section . "'");
	}

	/**
	 * Add a debug entry. Objects will be converted to strings with var_export();
	 * 
	 * You can also provide a closure function so code will only be executed when
	 * debugging is enabled.
	 *
	 * @todo if for some reason an error occurs here then an infinite loop is created
	 * @param callable|string|object $mixed
	 * @param string $type The type of message. Types can be arbitrary and can be enabled and disabled for output. {@see self::$enabledTypes}
	 */
	public function debug($mixed, $type = self::TYPE_GENERAL, $traceBackSteps = 0) {

		if(!$this->enabled ) {// || !in_array($this->section, $this->enabledSections) || !in_array($type, $this->enabledTypes)) {
			return;
		}		
		
		if($mixed instanceof \Closure) {
			$mixed = call_user_func($mixed);
		}elseif (!is_scalar($mixed)) {
			$mixed = print_r($mixed, true);
		}
		
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 6 + $traceBackSteps);
		
//		var_dump($bt);
		$lastCaller = null;
		$caller = array_shift($bt);
		//can be called with \go\core\App::get()->debug(). We need to go one step back (no class for closure)
		while(isset($caller['class']) && ($caller['function'] == 'debug' || $caller['class'] == self::class)) {
			$lastCaller = $caller;
			$caller = array_shift($bt);
		}
		
		$count = count($bt);
		
		$traceBackSteps = min([$count, $traceBackSteps]);
		
		while($traceBackSteps > 0) {			

			$caller = array_shift($bt);
			$traceBackSteps--;			
		}
		
		if(empty($caller['class'])) {
			
			$caller['class'] = 'closure';
		}
		
		if(!isset($caller['line'])) {
			$caller['line'] = '[unknown line]';
		}
		
		$entry = "[" . $this->getTimeStamp() . "][" . $caller['class'] . ":".$lastCaller['line']."] " . $mixed;

		if(!empty($this->logPath)) {
			$debugLog = new Fs\File($this->logPath);

			if($debugLog->isWritable()) {
				$debugLog->putContents($entry."\n", FILE_APPEND);
			}
		}

		
		$this->entries[] = $entry;
		
	}

	/**
	 * Add a message that notes the time since the request started in milliseconds
	 * 
	 * @param string $message
	 */
	public function debugTiming($message) {
		$this->debug($this->getTimeStamp() . ' ' . $message, 'timing');
	}

	private function getTimeStamp() {
		return intval(($this->getMicroTime() - $_SERVER["REQUEST_TIME_FLOAT"])*1000) . 'ms';
	}

	public function debugCalledFrom($limit = 10) {

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
