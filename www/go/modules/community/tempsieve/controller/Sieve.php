<?php

namespace go\modules\community\tempsieve\controller;

use go\core\Controller;
use go\core\exception\Unauthorized;
use go\core\http\Exception;

final class Sieve extends Controller
{
	use SieveControllerTrait;


	/**
	 * @param array $params
	 * @return true[]
	 * @throws Exception
	 * @throws Unauthorized
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