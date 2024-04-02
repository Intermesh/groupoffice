<?php
namespace go\modules\community\pwned\model;

use Exception;
use go\core\ErrorHandler;
use go\core\http\Client;
use Throwable;

/**
 * Util to check if a password has been breached
 *
 * @example
 *
 * $p = new Pwned();
 * $pwned = $p->hasBeenPwned($password);
 *
 * if($pwned) {
 * throw new Exception("You have been pwned");
 * }
 */
class Pwned {
	private int $rangeSize = 5;

	private string $endPoint = 'https://api.pwnedpasswords.com/range/';

	private function split(string $password): array
	{
		$value = strtoupper(sha1($password));

		return [
			substr($value, 0, $this->rangeSize),
			substr($value, $this->rangeSize)
		];
	}

	/**
	 * Check if a password is in the haveibeenpwd database
	 *
	 * @param string $password
	 * @return int Number of breaches
	 * @throws Exception
	 */
	public function hasBeenPwned(string $password): int
	{
		list($range, $selector) = $this->split($password);

		$client = new Client();

		try {
			$response = $client->get($this->endPoint . $range);
		} catch(Throwable $e) {
			// We don't want to block login if the API is down or whatsoever.
			ErrorHandler::logException($e, "Have I Been Pwned check failed");
			return 0;
		}

		if (empty($response['body'])) {
			return 0;
		}

		$lines = explode("\n", trim($response['body']));

		if (empty($lines)) {
			return 0;
		}

		foreach ($lines as $line) {
			list($pwnedSelector, $count) = explode(":", trim($line));
			if($pwnedSelector == $selector) {
				return (int) $count;
			}
		}

		return 0;
	}
}