<?php
/**
 * @see https://www.rfc-editor.org/rfc/rfc9661.html
 * @see https://stalw.art/docs/sieve/jmap
 */
namespace go\modules\community\tempsieve\controller;

use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\jmap\EntityController;
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
		if(isset($p['ids']) && !count($p['ids'])) {
			return $result;
		}
		$activeScript = $this->sieve->getActive($this->accountId);
		if (!isset($p['ids'])) {
			$p['ids'] = $this->sieve->getSieveScripts();
		}

		$foundIds = $p['ids'];

		foreach($p['ids'] as $currentScript) {
			$this->sieve->load($currentScript);

			$curr = $this->sieve->script->content[$params['index']];

			// make sure the entity result has an ID property. Otherwise it's difficult to identify objects in the "list" array.
			$m = new model\SieveScript();
			$m->id = $currentScript;
			$m->name = $currentScript;
			$m->blobId =  $curr; // TODO: Save as a blob
			$m->isActive =$currentScript === $activeScript;
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
}