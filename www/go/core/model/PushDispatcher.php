<?php

namespace go\core\model;

use go\core\event\EventEmitterTrait;
use go\core\orm\EntityType;
use go\core\db\Query;

/**
 * Class PushDispatcher
 * This is used by the sse.php endpoint.
 * It dispatches server sent events to the client
 * It fires an 'interval' event every time it checks for updates
 * @package go\core\model
 */
class PushDispatcher
{
	use EventEmitterTrait;

	const MAX_LIFE_TIME = 120;
	const EVENT_INTERVAL = 'interval';

	private $interval;

	private $map = [];
	private $entityTypes = [];

	public function __construct($types)
	{
		$this->interval = go()->getDebugger()->enabled ? 5 : 30;

		//Hard code debug to false to prevent spamming of log.
		go()->getDebugger()->enabled = false;

		$query = new Query();

		if(isset($types)) {
			$entityNames = explode(",", $_GET['types']);
			$query->where('e.clientName', 'IN', $entityNames);
		}
		$entities = EntityType::findAll($query);
		foreach($entities as $e) {
			$this->map[$e->getName()] = $e->getClassName();
			$this->entityTypes[$e->getId()] = $e->getName();
		}

		$this->sendMessage('ping', []);
	}

	/**
	 * Only use this method in the event listeners that are attached to this dispatcher
	 * @param $type string type of SSE event
	 * @param $data mixed a jsonSerializable object
	 */
	public function sendMessage($type, $data) {
		echo "event: $type\n";
		echo 'data: ' . json_encode($data). "\n\n";

		while(ob_get_level() > 0) {
			ob_end_flush();
		}

		flush();
	}

	private function checkChanges() {
		$state = [];
		foreach ($this->map as $name => $cls) {
			$cls::entityType()->clearCache();
			$state[$name] = $cls::getState();
		}
		// sendMessage('ping', $state);
		return $state;
	}


	private function diff($old, $new) {

		$diff = [];

		foreach ($new as $key => $value) {
			if (!isset($old[$key]) || $old[$key] !== $value) {
				$diff[$key] = $value;
			}
		}

		return $diff;
	}

	public function start($ping = 10) {
		$sleeping = 0;
		$changes = $this->checkChanges();

		$start = time();
		for($i = 0; $i < self::MAX_LIFE_TIME; $i += $this->interval) {
			// break the loop if the client aborted the connection (closed the page)
			if(connection_aborted()) {
				break;
			}

			// sendMessage('test', [$sleeping, $ping]);
			if ($sleeping >= $ping) {
				$sleeping = 0;
				$this->sendMessage('ping', []);
			}

			$new = $this->checkChanges();
			$diff = $this->diff($changes, $new);
			if(!empty($diff)) {
				$sleeping = 0;
				$this->sendMessage('state', $diff);
				$changes = $new;
			}

			self::fireEvent(self::EVENT_INTERVAL, $this);

			go()->getDbConnection()->disconnect();

			$sleeping += $this->interval;
			time_sleep_until($start+$i+$this->interval);
		}
	}
}