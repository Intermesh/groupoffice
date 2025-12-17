<?php

namespace go\modules\community\tempsieve\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\tempsieve\model;

final class Rule extends EntityController
{
	use SieveControllerTrait;

	/**
	 * @inheritDoc
	 */
	protected function entityClass(): string
	{
		return model\Rule::class;

	}


	/**
	 * @throws InvalidArguments
	 * /
	 * public function query(array $params): \go\core\util\ArrayObject
	 * {
	 * try {
	 * $p = $this->paramsQuery($params);
	 * $this->checkInput($params);
	 *
	 * $state = $this->getState();
	 *
	 * $response = new ArrayObject([
	 * 'accountId' => $p['accountId'],
	 * 'state' => $state, //deprecated
	 * 'queryState' => $state,
	 * 'ids' => $this->sieve->getSieveScripts(),
	 * 'notfound' => [],
	 * 'canCalculateUpdates' => false
	 * ]);
	 * } catch (\Exception $e) {
	 * throw $e; // TODO: convert this into GOUI-readable output
	 * }
	 *
	 * return $response;
	 * }
	 */

	/**
	 * @throws \Exception
	 */
	public function get(array $params): \go\core\util\ArrayObject
	{
		$this->checkInput($params);
		if (isset($params['filter']['scriptName'])) {
			$scriptName = $params['filter']['scriptName'];
		} else {
			$scriptName = $this->sieve->getActive($params['accountId']);
		}

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

		/*
		$rules = array();

				$this->sieve->load($scriptName);
				if (isset($params['delete_keys'])) {
					try {
						$keys = json_decode($params['delete_keys']);

						foreach ($keys as $key) {
							if ($this->sieve->script->delete_rule($key)) {
								$this->sieve->save();
							}
						}
						$response['deleteSuccess'] = true;
					} catch (\Exception $e) {
						$response['deleteSuccess'] = false;
						$response['deleteFeedback'] = $e->getMessage();
					}
				}

				if (!empty($this->sieve->script->content)) {
					foreach ($this->sieve->script->content as $index => $item) {
						// Hide the "Out of office" script because it need to be loaded in a separate dialog
						if (isset($item['name']) && $item['name'] != 'Out of office') {
							$i['name'] = $item['name'];
							$i['index'] = $index;
							$i['script_name'] = $scriptName;
							$i['active'] = !$item['disabled'];
							$rules[] = $i;
						}
					}
				} */
		$this->sieve->load($scriptName);
		$content = array_filter($this->sieve->script->content, function ($item) {
			return $item['name'] !== 'Out of office';
		});
		$rules = [];
		$notFound = [];
		if (empty($content)) {
			$notFound = $p['ids'];
		}
		if (!isset($p['ids'])) {
			$p['ids'] = range(0, count($this->sieve->script->content) - 1);
		}

		foreach ($p['ids'] as $idx) {
			// make sure the entity result has an ID property. Otherwise it's difficult to identify objects in the "list" array.
			if (isset($content[$idx])) {
				$item = $content[$idx];
				$item['scriptName'] = $scriptName;
				$rules[$idx] = $item;
			} else {
				$notFound[] = $idx;
			}
		}
		$result['list'] = array_values($rules);
		$result['notFound'] = $notFound;

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