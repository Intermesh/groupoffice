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
	private int $CHECK_INTERVAL = 20;


	/**
	 * SSE uses apcu if available to check changes every second. When an entity is modified a state counter is
	 * incremented and SSE will check if the change was relevant for the user via DB queries. This keeps to amount of
	 * DB queries for SSE to a minimum
	 *
	 * @var bool
	 */
	private bool $apcuEnabled = false;

	/**
	 * @var string[]
	 */
	private array $entities = [];

	public function __construct(array $entities = [])
	{
		if(function_exists("apcu_fetch")) {
			$this->apcuEnabled = true;
			$this->CHECK_INTERVAL = 1;
		}

		// disable default disconnect checks
		ignore_user_abort(true);


		// LogEntry, Search and user get lots of updates. We only update them when needed,
		// On large systems getting the user updates caused very high load becuase it constantly changes.
		// this lead to lots of User/changes calls per second while we almost never need the user entity to be up to date.
		// only your own user when checking your account settings.
		$this->entities = array_filter($entities, function($name) {
			return $name != "User" && $name != "Search" && $name != 'LogEntry';
		});

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

	private function shouldCheckDB(string $name): bool
	{
		if(!$this->apcuEnabled) {
			return true;
		}

		$current = $this->getStateCounter($name);

		$check =  $current !== $this->counters[$name];

		$this->counters[$name] = $current;

		return $check;
	}

	private function checkChanges(): array
	{
		$closeDb = false;
		$state = [];
		foreach ($this->entities as $name) {

			$entityType = EntityType::findByName($name);

			if(!isset($this->counters[$name])) {
				$this->counters[$name] = 0;
			}

			if($this->shouldCheckDB($name)) {

				go()->debug('PushDispatcher::checkChanges() on DB for '. $name);
				/** @var Entity $cls */
				$entityType->clearCache();
				$cls = $entityType->getClassName();
				$state[$name] = $cls::getState();

				$closeDb = true;
			}
		}

		if($closeDb) {
			//disconnect and free up memory
			go()->getDbConnection()->disconnect();

			// We want to preserve the EntityType instances otherwise states won't be correctly checked
			go()->getCache()->freeMemory(['entity-types']);

			Table::destroyInstances();
			gc_collect_cycles();
		}

		return $state;
	}

	private function getStateCounter(string $name) : int {
		$cnt =  apcu_fetch('state_sse_'. $name);
		if($cnt === false) {
			return self::incStateCounter($name);
		}

		return $cnt;
	}

	/**
	 * Will cause all SSE connections to query the state from DB
	 *
	 * @param string $name
	 * @return void
	 */
	public static function incStateCounter(string $name) : int {
//		go()->debug("PushDispatcher::incStateCounter($name)");
		if(function_exists("apcu_inc")) {
			return apcu_inc('state_sse_' . $name);
		}else {
			return 0;
		}
	}


	private array $counters = [];


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

	public function start(int $ping = 5): void
	{
		// because there are always many sse requests simultaneously we must keep memory as low as possible.
		//go()->getCache()->disableMemory();

		$sleeping = 0;
		$changes = $this->checkChanges();
		// send states on start so client can compare immediately
		$this->sendMessage('state', $changes);
		for($i = 0; $i < self::MAX_LIFE_TIME; $i += $this->CHECK_INTERVAL) {

			if ($sleeping >= $ping) {
				$sleeping = 0;
				$this->sendMessage('ping', []);
			}

			// break the loop if the client aborted the connection (closed the page)
			if(connection_aborted()) {
				go()->debug("PushDispatcher::SSE connection aborted by client");
				break;
			}

			$new = $this->checkChanges();

			$diff = $this->diff($changes, $new);
			if(!empty($diff)) {
				$sleeping = 0;
				$this->sendMessage('state', $diff);
				$changes = array_merge($changes, $new);
			}

			self::fireEvent(self::EVENT_INTERVAL, $this);

//			go()->debug("SSE Memory usage: " . memory_get_usage());

			$sleeping += $this->CHECK_INTERVAL;

			sleep($this->CHECK_INTERVAL);
		}
	}
}