<?php

namespace go\modules\community\tempsieve\controller;

use go\core\Controller;
use go\core\exception\Unauthorized;
use go\core\http\Exception;
use go\modules\community\email\model\Account;
use go\modules\community\tempsieve\util\Sieve as SieveUtil;

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

	/**
	 * Retrieve a single Sieve rule
	 *
	 * @param array $params
	 * @return array
	 * @throws Exception
	 * @throws Unauthorized
	 * /
	public function rule(array $params): array
	{
		$this->checkInput($params);

		$response = [
			'success' => true,
			'criteria' => [],
			'actions' => []
		];
		$this->sieve->load($params['scriptName']);

		$curr = $this->sieve->script->content[$params['index']];

		$joinType = 'anyof';
		if ($curr['join'] == 1) {
			$joinType = 'allof';
		} else if ($curr['join'] == '' && $curr['tests'][0]['test'] == 'true') {
			$joinType = 'any';
		}
		$response['data']['join'] = $joinType;

		$response['data']['active'] = !$curr['disabled'];
		$response['data']['rule_name'] = $curr['name'];

		foreach ($curr['tests'] as $test) {
			$response['criteria'][] = $test;
		}

		foreach ($curr['actions'] as $action) {
			switch ($action['type']) {
				case 'addflag':
					if ($action['target'] == '\\Seen') {
						$action['text'] = "Mark message as read";
					}
					break;
				case 'set_read':
					$action['text'] = go()->t("Mark message as read", "sieve");
					break;
				case 'fileinto':
					if (empty($action['copy'])) {
						$action['text'] = go()->t("Move email to the folder", "sieve") . ' "' . $action['target'] . '"';
					} else {
						$action['text'] = go()->t("Copy email to the folder", "sieve") . ' "' . $action['target'] . '"';
						$action['type'] = 'fileinto_copy';
					}
					break;

				case 'redirect':
					if (!empty($action['copy'])) {
						$action['type'] = 'redirect_copy';
						$action['text'] = go()->t("Send a copy to", "sieve") . ' "' . $action['target'] . '"';
					} else {
						$action['text'] = go()->t("Redirect to", "sieve") . ' "' . $action['target'] . '"';
					}
					break;
				case 'reject':
					$action['text'] = go()->t("Reject with message:", "sieve") . ' "' . $action['target'] . '"';
					break;
				case 'vacation':
					$addressesText = !empty($action['addresses']) && is_array($action['addresses'])
						? go()->t("Autoreply is active for", "sieve") . ': ' . implode(',', $action['addresses']) . '. '
						: '';

					if (empty($action['days'])) {
						$action['days'] = 7;
					}

					$action['text'] = go()->t("Reply every", "sieve") . ' ' . $action['days'] . ' ' . go()->t("day(s)", "sieve") . '. ' . $addressesText . go()->t("Message:", "sieve") . ' "' . $action['reason'] . '"';
					break;
				case 'discard':
					$action['text'] = go()->t("Discard", "sieve");
					break;
				case 'stop':
					$action['text'] = go()->t("Stop", "sieve");
					break;
				default:
					$action['text'] = go()->t("Error while displaying test line", "sieve");
					break;
			}
			$response['actions'][] = $action;
		}
		return $response;
	}
*/
	/**
	 * Retrieve sieve rules for currently selected account
	 *
	 * @param array $params
	 * @return array
	 * @throws Exception
	 * @throws Unauthorized
	 * @throws \Exception
	 * /
	public function rules(array $params): array
	{
		$this->checkInput($params);
		if (!empty($params['script_name'])) {
			$scriptName = $params['script_name'];
		} else {
			$scriptName = $this->sieve->getActive($params['accountId']);
		}

		$response = ['success' => false];

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
		}
		$response['rules'] = $rules;
		$response['success'] = true;
		return $response;
	}
*/
}