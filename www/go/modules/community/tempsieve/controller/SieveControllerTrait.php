<?php

namespace go\modules\community\tempsieve\controller;

use go\core\exception\Unauthorized;
use go\core\http\Exception;
use go\modules\community\email\model\Account;
use go\modules\community\tempsieve\util\Sieve as SieveUtil;

trait SieveControllerTrait
{
	private ?string $sieveError;
	private SieveUtil $sieve;

	private ?string $accountId;

	public function __construct()
	{
		parent::__construct();
		$this->sieve = new SieveUtil();
	}
	/**
	 * Simple validator for incoming parameters
	 *
	 * Check whether at least an account is given. Also try to log in using said account.
	 *
	 * @param array $params
	 * @return void
	 * @throws Exception
	 * @throws Unauthorized
	 */
	private function checkInput(array $params, ?array $extraParams = []): void
	{
		$filters = $params['filter'] ?? [];
		if(!isset($params['accountId'])) {
			throw new Exception(412, "Required 'accountId' parameter missing");
		}
		foreach($extraParams as $extraParam) {
			if(!isset($params[$extraParam])) {
				throw new Exception(412, "Required '{$extraParam}' parameter missing");
			}
		}
		if (!$this->connect($params['accountId'])) {
			throw new Exception(412, "Failed to connect to account '{$params['accountId']}'");
		}
	}

	/**
	 * @return bool
	 * @throws \Exception
	 * @throws Unauthorized
	 */
	private function connect(string $accountId): bool
	{
		$this->accountId = $accountId;

		$accountModel = Account::findById($accountId);

		if (!empty($accountModel)) {
			$connectResponse = $this->sieve->connect(
				$accountModel->username,
				$accountModel->decryptPassword(),
				$accountModel->host,
				$accountModel->sieve_port,
				null,
				!empty($accountModel->sieve_usetls)
			);
		}
		if (empty($connectResponse)) {
			$this->sieveError = 'Sorry, manage sieve filtering not supported on ' . $accountModel->host . ' using port ' . $accountModel->sieve_port;
			return false;
		}

		return true;

	}
}