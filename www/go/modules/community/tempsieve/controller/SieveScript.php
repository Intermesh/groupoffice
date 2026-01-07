<?php
/**
 * @see https://www.rfc-editor.org/rfc/rfc9661.html
 * @see https://stalw.art/docs/sieve/jmap
 */

namespace go\modules\community\tempsieve\controller;

use go\core\fs\Blob;
use go\core\http\Exception;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\jmap\EntityController;
use go\core\jmap\SetError;
use go\core\util\ArrayObject;
use go\core\util\Lock;
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
	 * @throws \Exception
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5804
	 */
	public function set(array $params): \go\core\util\ArrayObject
	{
		$this->checkInput($params);

		$p = $this->paramsSet($params);

		// make sure there are no concurrent set request to avoid clients missing states
		$lock = new Lock("jmap-set-lock");
		if (!$lock->lock()) {
			throw new Exception("Could not obtain lock");
		}

		static::fireEvent(self::EVENT_BEFORE_SET, $this, $p);

		$oldState = $this->getState();

		if (isset($p['ifInState']) && $p['ifInState'] != $oldState) {
			throw new StateMismatch("State mismatch. The server state " . $oldState . ' does not match your state ' . $p['ifInState']);
		}

		$result = new ArrayObject([
			'accountId' => $p['accountId'],
			'created' => null,
			'updated' => null,
			'destroyed' => null,
			'notCreated' => null,
			'notUpdated' => null,
			'notDestroyed' => null,
		]);

//		$this->createEntitites($p['create'], $result); TODO
//		$this->destroyEntities($p['destroy'], $result); TODO
		foreach ($p['update'] as $id => $properties) {
			$loaded = $this->sieve->load($id);
			if (!$loaded) {
				$result['notUpdated'][$id] = new SetError('notFound', go()->t("Item not found"));
				continue;
			}
			$entity = new model\SieveScript();
			$entity->setValues($properties);
			if (!$this->canUpdate($entity)) {
				$result['notUpdated'][$id] = new SetError("forbidden", go()->t("Permission denied"));
				continue;
			}
			$b = Blob::findById($properties['blobId']);
			if (!$b) {
				$result['notUpdated'][$id] = new SetError('notFound', go()->t("Blob not found"));
				continue;
			}

			if (!$this->sieve->saveBlob($id, $b)) {
				$result['notUpdated'][$id] = new SetError("invalidSieve");
				$result['notUpdated'][$id]->properties = ['blobId'];
				$result['notUpdated'][$id]->validationErrors = $this->sieve->getError();
				continue;
			}

			//The server must return all properties that were changed during a create or update operation for the JMAP spec
			$diff = ['blobId' => $properties['blobId']];
			// RFC9661: if the onSuccessActivateScript parameter is set, explicitly activate the script and set the isActive property to true
			if (isset($p['onSuccessActivateScript'])) {
				$this->sieve->activate($p['onSuccessActivateScript']);
				$diff['isActive'] = true;
			} elseif(isset($p['onSuccessDeactivateScript'])) {
				$this->sieve->deactivate(); // Iterate through all the sieve scriets?
				$diff['isActive'] = false;
			}

			$result['updated'][$id] = empty($diff) ? null : $diff;
		}


		$result['oldState'] = $oldState;
		$result['newState'] = $this->getState();

		static::fireEvent(self::EVENT_SET, $this, $p, $result);
		$lock->unlock();
		return $result;
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

		$setError = null;
		if (!$this->sieve->validate()) {
			$setError = new SetError('invalidSieve', $this->sieve->getError());
		}
		return new ArrayObject([
			'accountId' => $p['accountId'],
			'error' => $setError
		]);
	}

	/**
	 * For now, this is always possible, but it should be tied to the ACL of the account entity in the new email module
	 *
	 * @param $entity
	 * @return bool
	 */
	protected function canUpdate($entity): bool
	{
		return true;
	}
}