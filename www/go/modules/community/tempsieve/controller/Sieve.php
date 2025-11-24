<?php

namespace go\modules\community\tempsieve\controller;

use go\core\Controller;
use go\core\exception\Unauthorized;
use go\core\http\Exception;
use go\core\util\ArrayObject;
use go\modules\community\email\model\Account;
use go\modules\community\tempsieve\util\Sieve as SieveUtil;

final class Sieve extends Controller
{

	private ?string $sieveError;
	private SieveUtil $sieve;

	public function __construct()
	{
		parent::__construct();
		$this->sieve = new SieveUtil();
	}

	/**
	 * @throws Unauthorized
	 * @throws \Exception
	 * @return bool
	 */
	private function connect(string $accountId): bool
	{
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

	/**
	 * @param array $params
	 * @return true[]
	 * @throws Exception
	 */
	public function isSupported(array $params): array
	{
		if (!isset($params['accountId'])) {
			throw new Exception(412, "Required 'accountId' parameter missing");
		}
		$isSupported = $this->connect($params['accountId']);

		return ['success' => true, 'isSupported' => $isSupported, 'message' => $this->sieveError ?? null];
	}
}