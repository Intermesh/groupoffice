<?php

namespace GO\Base\Util;


class Cli {

	/**
	 * Parse command line arguments in named variables.
	 * 
	 * eg.
	 * php index.php -r=maintenenance/upgrade --someParam=value
	 * 
	 * will return array('r'=>'maintenance/upgrade','someParam'=>'value');
	 * 
	 * @return array 
	 */
	public static function parseArgs() {
		global $argv;

		//array_shift($argv);
		$out = array();
		$count = count($argv);
		if ($count > 1) {
			for ($i = 1; $i < $count; $i++) {
				$arg = $argv[$i];
				if (substr($arg, 0, 2) == '--') {
					$eqPos = strpos($arg, '=');
					if ($eqPos === false) {
						$key = substr($arg, 2);
						$out[$key] = isset($out[$key]) ? $out[$key] : true;
					} else {
						$key = substr($arg, 2, $eqPos - 2);
						$out[$key] = substr($arg, $eqPos + 1);
					}
				} else if (substr($arg, 0, 1) == '-') {
					if (substr($arg, 2, 1) == '=') {
						$key = substr($arg, 1, 1);
						$out[$key] = substr($arg, 3);
					} else {
						$chars = str_split(substr($arg, 1));
						foreach ($chars as $char) {
							$key = $char;
							$out[$key] = isset($out[$key]) ? $out[$key] : true;
						}
					}
				} else {
					$out[] = $arg;
				}
			}
		}
		return $out;
	}
	
	/**
	 * Prompt for user input
	 * 
	 * @param StringHelper $text
	 * @return StringHelper User input
	 */
	public static function passwordPrompt($text){
		
		$command = "/usr/bin/env bash -c 'echo OK'";
		if (rtrim(shell_exec($command)) !== 'OK') {
			trigger_error("Can't invoke bash to get prompt", E_USER_ERROR);
		}
		$command = "/usr/bin/env bash -c 'read -s";
		
		$command .= " -p";
		
		$command .= " \"";
		
		$command .= $text
						. "\" mypassword && echo \$mypassword'";

		$input =  rtrim(shell_exec($command));
		
		echo "\n";
		
		return $input;
	}
	
	
	public static function getScriptPath() {
		$output = array();
		exec('pwd', $output);
		$path = $output[0].'/'.$_SERVER["SCRIPT_FILENAME"];
		$parts = explode('/', $path);

		for($i = 0, $c = count($parts); $i < $c; $i++) {
			if($parts[$i] == '..') {
				$parts[$i - 1] = null;
				$parts[$i] = null;
			}
		}

		$path = implode('/', array_filter($parts, function($part){
			return isset($part);
		}));
		
		return $path;
	}

}
