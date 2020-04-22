<?php
namespace go\core\orm;

use Exception;
use go\core\model\Log;
use go\core\model\Module;

trait LoggingTrait {

	public static $enabled = true;

	/**
	 * Get the message for the log module. Returns the contents of the first text column by default.
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getLogMessage($action){

		if(!method_exists($this, 'getSearchName')) {
			throw new Exception("The LoggingTrait depends on the SearchAble triat. Please implement that too.");
		}

		$msg = $this->getSearchName();
		$desc = $this->getSearchDescription();
		if($desc) {
			$msg .= "\n".$desc;
		}
		
		return $msg;
	}

	/**
	 * Get the JSON data string for the given log action
	 * 
	 * @param string $action
	 * @return array Data for the JSON string 
	 */
	private function getLogJSON($action) {



		switch ($action) {
			case Log::ACTION_DELETE:
				return $this->cutLengths($this->toArray());
			case Log::ACTION_ADD:
			case Log::ACTION_UPDATE:
				$attrs = $this->getModified();				
				
				$cutoffString = ' ..Cut off at 500 chars.';
				$cutoffLength = 500;

				foreach ($attrs as $attr => $val) {
					
					if ((isset($val[0]) && !is_scalar($val[0])) || (isset($val[1]) && !is_scalar($val[1]))) {
						unset($attrs[$attr]);
						continue;
					}

					if (strlen($val[0]) > $cutoffLength) {
						$attrs[$attr][0] = substr($val[0], 0, $cutoffLength) . $cutoffString;
					}
					
					if (strlen($val[1]) > $cutoffLength) {
						$attrs[$attr][1] = substr($val[1], 0, $cutoffLength) . $cutoffString;
					}	
				}

				return $attrs;
				
		}

		return array();
	}
	
	private function cutLengths($attrs) {		
		$cutoffString = ' ..Cut off at 500 chars.';
		$cutoffLength = 500;
		
		foreach ($attrs as $attr => $val) {
			if (!is_scalar($val)) {
				unset($attrs[$attr]);
				continue;
			}

			if (strlen($val) > $cutoffLength) {
				$attrs[$attr] = substr($val, 0, $cutoffLength) . $cutoffString;
			}				
		}

		return $attrs;
	}

	/**
	 * Will all a log record in go_log
	 * @param string $action
	 * @return boolean returns the created log or succuss status when save is true
	 * @throws Exception
	 */
	protected function log($action) {

		if(!self::$enabled) {
			return true;
		}
	
		$message = $this->getLogMessage($action);
		if ($message && Module::findByName(null, 'log')) {

			$data = $this->getLogJSON($action);

			$log = new Log();

			$log->model_id = $this->id;

			$log->action = $action;
			$log->model = $this->entityType()->getName();
			$log->message = $message;
			//$log->object = $this;
			$log->jsonData = json_encode($data);

			$log->cutPropertiesToColumnLength();
			
			if(!$log->save()) {
				throw new Exception("Could not log! " . var_export($log->getValidationErrors(), true));
			}
		}
		
		return true;
	}

	protected static function logDelete(Query $query) {

		if(!self::$enabled) {
			return true;
		}

		$pdo = go()->getDbConnection()->getPDO();

		if (PHP_SAPI == 'cli') {
			$user_agent = '"cli"';
		} else {
			$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $pdo->quote($_SERVER['HTTP_USER_AGENT']) : '"unknown"';
		}

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $pdo->quote($_SERVER['REMOTE_ADDR']) : '""';
		$controller_route = '"JMAP"';
		$username = $pdo->quote(go()->getDbConnection()->selectSingleValue('username')->from('core_user')->where('id', '=', go()->getUserId())->single());
		$user_id = go()->getUserId() ?? 1;
		$ctime = time();
		$entity = $pdo->quote(static::entityType()->getName());

		return go()->getDbConnection()->insert(
			'go_log', 
			\go()->getDbConnection()
						->select("`name` AS message, entityId as model_id, $user_agent, $ip, $controller_route, $username, $user_id, $ctime, 'delete', $entity")
						->from('core_search')
						->where(['entityTypeId' => static::entityType()->getId()])
						->andWhere('entityId', 'IN', $query),
			['message', 'model_id', "user_agent", "ip", "controller_route", "username", "user_id", "ctime", "action", "model"])
			->execute();
	}

}
