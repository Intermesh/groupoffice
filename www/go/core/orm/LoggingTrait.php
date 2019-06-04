<?php
namespace go\core\orm;

use go\core\model\Log;
use go\core\model\Module;

trait LoggingTrait {
	
	/**
	 * Get the message for the log module. Returns the contents of the first text column by default.
	 *
	 * @return string
	 */
	public function getLogMessage($action){

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
	 */
	protected function log($action) {
	
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
			
			if(!$log->save()) {
				throw new \Exception("Could not log! " . var_export($log->getValidationErrors(), true));
			}
		}
		
		return true;
	}

}
