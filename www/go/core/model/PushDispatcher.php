<?php

namespace go\core\model;

use go\core\App;
use go\core\db\Table;
use go\core\event\EventEmitterTrait;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\db\Query;
use go\core\orm\Property;

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

	/**
	 * Event fired at every time the pushdispatcher checks the state for changes
	 */
	const EVENT_INTERVAL = 'interval';
	/**
	 * Life time of the SSE request in seconds
	 */
	const MAX_LIFE_TIME = 120;

	/**
	 * Interval in seconds between every check for changes to push
	 */
	const CHECK_INTERVAL = 30;

	/**
	 * @var EntityType[]
	 */
	private array $map = [];

	public function __construct(array $types = [])
	{
		//Hard code debug to false to prevent spamming of log.
//		go()->getDebugger()->enabled = false;

		$query = new Query();

		// Search and user get lots of updates. We only update them when needed,
		// On large systems getting the user updates caused very high load becuase it constantly changes.
		// this lead to lots of User/changes calls per second while we almost never need the user entity to be up to date.
		// only your own user when checking your account settings.
		$types = array_filter($types, function($name) {
			return $name != "User" && $name != "Search";
		});

		if(!empty($types)) {
			$query->where('e.clientName', 'IN', $types);
		}

		$entities = EntityType::findAll($query);
		foreach($entities as $e) {
			if(is_a($e->getClassName(), Entity::class, true)) {
				$this->map[$e->getName()] = $e;
			}
		}
	}

	/**
	 * Only use this method in the event listeners that are attached to this dispatcher
	 * @param string $type string type of SSE event
	 * @param mixed $data mixed a jsonSerializable object
	 */
	public function sendMessage(string $type, mixed $data): void
	{
		echo "event: $type\n";
		echo 'data: ' . json_encode($data). "\n\n";

		while(ob_get_level() > 0) {
			ob_end_flush();
		}

		flush();
	}

	private function checkChanges(): array
	{
		$state = [];
		foreach ($this->map as $name => $entityType) {
			/** @var Entity $cls */
			$entityType->clearCache();
			$cls = $entityType->getClassName();
			$state[$name] = $cls::getState();
		}

		return $state;
	}


	private function diff(array $old, array $new): array
	{
		$diff = [];

		foreach ($new as $key => $value) {
			if (!isset($old[$key]) || $old[$key] !== $value) {
				$diff[$key] = $value;
			}
		}

		return $diff;
	}

	public function start(int $ping = 10): void
	{
		$sleeping = 0;
		$changes = $this->checkChanges();
		// send states on start so client can compare immediately
		$this->sendMessage('state', $changes);
		for($i = 0; $i < self::MAX_LIFE_TIME; $i += self::CHECK_INTERVAL) {
			// break the loop if the client aborted the connection (closed the page)
			if(connection_aborted()) {
				go()->debug("SSE connection aborted by client");
				break;
			}

			// sendMessage('test', [$sleeping, $ping]);
			if ($sleeping >= $ping) {
				$sleeping = 0;
				$this->sendMessage('ping', []);
			}

			$new = $this->checkChanges();
//			go()->debug($new);
			$diff = $this->diff($changes, $new);
			if(!empty($diff)) {
				$sleeping = 0;
				$this->sendMessage('state', $diff);
				$changes = $new;
			}

			self::fireEvent(self::EVENT_INTERVAL, $this);

			//disconnect and free up memory
			go()->getDebugger()->debug("Closing DB connection: " . go()->getDbConnection()->getId());
			go()->getDbConnection()->disconnect();

			// because there are always many sse requests simultaneously we must keep memory as low as possible.
			go()->getCache()->disableMemory();
			Table::destroyInstances();
			gc_collect_cycles();

			go()->debug("SSE Memory usage: " . memory_get_usage());

			$sleeping += self::CHECK_INTERVAL;

			sleep(self::CHECK_INTERVAL);
		}
	}
}