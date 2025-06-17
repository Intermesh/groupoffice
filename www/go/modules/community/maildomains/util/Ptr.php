<?php

namespace go\modules\community\maildomains\util;

use go\core\http\Exception;
use go\core\jmap\Request;
use go\core\util\ArrayObject;
use go\modules\community\maildomains\model\Settings;

final class Ptr
{
	/**
	 * Perform a PTR check using postconf and nslookup
	 *
	 * Example output:
	 * [
	 * ['ip' => '1.2.3.4', 'resolved' => 'smtp.example.com', 'status' => 'SUCCESS'],
	 * ['ip' => '11:22:33:44', 'resolved' => 'smtp.example.com', 'status' => 'FAILED']
	 * ]
	 *
	 * @param array $paramscurrently unused array of parameters
	 * @return ArrayObject
	 * @throws Exception
	 */
	public static function check(array $params): ArrayObject
	{

		$myhostname = Settings::get()->getMailHost();

		exec('nslookup '.escapeshellarg($myhostname), $output, $ret);
		if($ret !== 0) {
			throw new Exception(500, "Could not check PTR record. Error running nslookup for " . $myhostname . " " . implode(" ", $output));
		}

		$regex = '/^Name:\s+'.preg_quote($myhostname)."\s+Address:\s+([^\s]+)$/m";

		preg_match_all($regex, implode("\n",$output), $matches);

		$ips = $matches[1];

		$r = new ArrayObject();

		foreach($ips as $ip) {
			$resolved = gethostbyaddr($ip);

			$r->append([
				'ip' => $ip,
				'resolved' => $resolved,
				'status' => ($resolved === $myhostname) ? "SUCCESS" : "FAILED"
			]);
		}
		return $r;
	}
}