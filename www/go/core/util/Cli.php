<?php
namespace go\core\util;

class Cli {

	/**
	 * Prompt the user for input
	 *
	 * Requires bash environment
	 *
	 * @param string $question
	 * @param bool $password
	 * @return string
	 */
	public static function prompt(string $question, bool $password = false): string
	{
		$command = "/usr/bin/env bash -c 'echo OK'";
		if (rtrim(shell_exec($command)) !== 'OK') {
			trigger_error("Can't invoke bash to get prompt", E_USER_ERROR);
		}

		$command = "read ";

		if($password) {
			$command .= " -s";
		}

		$command .= " -p " . escapeshellarg($question." ") . " ";

		$command .= "input && echo \$input";

		$input =  rtrim(shell_exec("/usr/bin/env bash -c  " .  escapeshellarg($command)));

		echo "\n";

		return $input;
	}
}