<?php
/**
 * @see https://www.rfc-editor.org/rfc/rfc9661.html
 * @see https://stalw.art/docs/sieve/jmap
 */

namespace go\modules\community\tempsieve\controller;

use go\core\http\Exception;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\jmap\EntityController;
use go\core\jmap\SetError;
use go\core\util\ArrayObject;
use go\modules\community\tempsieve\model;

final class SieveScript extends EntityController
{
	use SieveControllerTrait;

	/**
	 * @inheritDoc
	 */
	protected function entityClass(): string
	{
		return model\SieveScript::class;
	}

	/**
	 * @throws InvalidArguments
	 */
	public function query(array $params): \go\core\util\ArrayObject
	{
		try {
			$p = $this->paramsQuery($params);
			$this->checkInput($params);

			$state = $this->getState();

			$response = new ArrayObject([
				'accountId' => $p['accountId'],
				'state' => $state, //deprecated
				'queryState' => $state,
				'ids' => $this->sieve->getSieveScripts(),
				'notfound' => [],
				'canCalculateUpdates' => false
			]);
		} catch (\Exception $e) {
			throw $e; // TODO: convert this into GOUI-readable output
		}

		return $response;
	}

	/**
	 * @throws \Exception
	 */
	public function get(array $params): \go\core\util\ArrayObject
	{
		$this->checkInput($params);

		$p = $this->paramsGet($params);

		$result = new ArrayObject([
			'accountId' => $p['accountId'],
			'state' => $this->getState(),
			'list' => [],
			'notFound' => []
		]);

		//empty array should return empty result. but ids == null should return all.
		if (isset($p['ids']) && !count($p['ids'])) {
			return $result;
		}
		$activeScript = $this->sieve->getActive($this->accountId);
		if (!isset($p['ids'])) {
			$p['ids'] = $this->sieve->getSieveScripts();
		}

		$foundIds = $p['ids'];
		$unsorted = [];

		foreach ($p['ids'] as $currentScript) {
			$this->sieve->load($currentScript);

			// make sure the entity result has an ID property. Otherwise it's difficult to identify objects in the "list" array.
			// For now, we return both the blob as the raw script. JMAP Sieve requires the blob, whereas we use old-fashioned
			// non-JMAP.
			// The SieveUtil class should probably distinguish between both scenarios. We'll get there when we get there.
			$m = new model\SieveScript();
			$m->setValues([
				'id' => $currentScript,
				'name' => $currentScript,
				'blobId' => $this->sieve->blobId,
				'script' => $this->sieve->rawScript,
				'isActive' => $currentScript == $activeScript
//				'extensions'=> $this->sieve->getSieveExtensions(
			]);
			$unsorted[] = $m;
			$foundIds[] = $currentScript;
		}
		$result['list'] = array_values($unsorted);
		$result['notFound'] = [];

		return $result;
	}

	/**
	 * @throws StateMismatch
	 * @throws InvalidArguments
	 * @TODO: Override using ManageSieve protocol
	 * @see https://datatracker.ietf.org/doc/html/rfc5804
	 */
	public function set(array $params): \go\core\util\ArrayObject
	{
		return $this->defaultSet($params);
	}

	/**
	 * @throws InvalidArguments
	 */
	public function changes(array $params): \go\core\util\ArrayObject
	{
		return $this->defaultChanges($params);
	}

	/**
	 * VValidate a sieve script
	 *
	 * @see https://www.rfc-editor.org/rfc/rfc9661.html#name-sievescript-validate
	 *
	 * @param array $params
	 * @return ArrayObject
	 * @throws Exception
	 * @throws \go\core\exception\Unauthorized
	 */
	public function validate(array $params): ArrayObject
	{
		$this->checkInput($params);
		if (!isset($params['rawScript'])) {
			$params['rawScript'] = '/ * empty script * /';
		}
		$p = $this->paramsGet($params);

		$this->sieve->setRawScript($params['rawScript']);

		// TODO: Do the actual validation
		$setError = null;
		if (!$this->sieve->validate()) {
			$setError = new SetError('invalidSieve', $this->sieve->getError());
		}
		return new ArrayObject([
			'accountId' => $p['accountId'],
			'error' => $setError
		]);
	}
}