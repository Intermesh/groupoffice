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
	private int $CHECK_INTERVAL;


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
		// the extension may be loaded but disabled (apc.enabled=0, CLI without apc.enable_cli)
		if(function_exists('apcu_enabled') && apcu_enabled()) {
			$this->apcuEnabled = true;
			// share the modseqs between all SSE connections
			EntityType::$sharedModSeqCache = true;
		}
		if (!empty(go()->getConfig()['sseCheckInterval']) && (int)go()->getConfig()['sseCheckInterval'] > 0) {
			$this->CHECK_INTERVAL = (int)go()->getConfig()['sseCheckInterval'];
		} else {
			$this->CHECK_INTERVAL = $this->apcuEnabled ? 1 : 20;
		}

		go()->getDbConnection()->debug = true;


		// spread the checks of all connections
		$this->recheckDelay = self::MIN_RECHECK_INTERVAL + mt_rand(0, 1000) / 1000;

		// disable default disconnect checks
		ignore_user_abort(true);


		// LogEntry, Search and user get lots of updates. We only update them when needed,
		// On large systems getting the user updates caused very high load becuase it constantly changes.
		// this lead to lots of User/changes calls per second while we almost never need the user entity to be up to date.
		// only your own user when checking your account settings.
		$this->entities = array_filter($entities, function($name) {
			return $name != "User" && $name != "Search" && $name != 'LogEntry' && EntityType::existsByName($name);
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

	/**
	 * Minimum time in seconds between two state checks of the same entity type per connection. A random jitter is added
	 * per connection. Bulk operations (imports, sync) increment the state counter many times per second. Without
	 * debouncing every connection would recompute its state on each increment.
	 */
	const MIN_RECHECK_INTERVAL = 2;

	/**
	 * Time of the last state check per entity name
	 *
	 * @var float[]
	 */
	private array $lastCheck = [];

	/**
	 * Debounce time for this connection including jitter
	 */
	private float $recheckDelay;

	private function shouldCheckDB(string $name): bool
	{
		if(!$this->apcuEnabled) {
			return true;
		}

		$current = $this->getStateCounter($name);

		if($current === $this->counters[$name]) {
			return false;
		}

		// Leading edge: the first change is handled immediately. Changes following too soon are postponed. The counter
		// is not stored when postponing so the check is done on a later tick, which also covers the trailing edge.
		$now = microtime(true);
		if($now - ($this->lastCheck[$name] ?? 0) < $this->recheckDelay) {
			return false;
		}

		$this->counters[$name] = $current;
		$this->lastCheck[$name] = $now;

		return true;
	}

	private function checkChanges(): array
	{
		$queriesBefore = EntityType::$modSeqQueries;
		$state = [];
		foreach ($this->entities as $name) {

			$entityType = EntityType::findByName($name);

			if(!isset($this->counters[$name])) {
				$this->counters[$name] = 0;
			}

			if($this->shouldCheckDB($name)) {
//				go()->debug('PushDispatcher::checkChanges() on DB for '. $name);
				/** @var Entity $cls */
				$entityType->clearCache();
				$cls = $entityType->getClassName();
				$state[$name] = $cls::getState();
			}
		}

		// With the shared modseq cache the DB is only used on a cache miss. Don't reconnect when it wasn't.
		$closeDb = EntityType::$modSeqQueries !== $queriesBefore;

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

	public static function getStateCounter(string $name) : int {
		$cnt = apcu_fetch(go()->getConfig()['db_name'] . '_state_sse_'. $name);
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
			return apcu_inc(go()->getConfig()['db_name'] . '_state_sse_' . $name);
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