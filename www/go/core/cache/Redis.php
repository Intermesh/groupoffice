<?php
namespace go\core\cache;

use go\core\ErrorHandler;
use go\core\exception\ConfigurationException;
use RuntimeException;

/**
 * Redis cache backend with:
 *
 * - per-instance namespacing using db_name
 * - per-instance namespace versioning stored in MySQL
 * - request-local memory cache
 * - ordered Redis failover across multiple servers (per request)
 * - explicit serialize()/unserialize() for payload compatibility
 * - fail-open behavior for ordinary cache operations
 * - atomic authentication counters (storage errors reported to the fail-open throttle)
 *
 * -------------------------------------------------------------------------
 * DESIGN DECISIONS
 * -------------------------------------------------------------------------
 *
 * 1. WHY REDIS INSTEAD OF MEMCACHED
 *
 * The old Memcached backend used flush(), which flushes the entire memcached
 * server. In an environment where one cache server holds many tenant instances,
 * that creates a platform-wide cold-cache event and can storm MySQL.
 *
 * This Redis backend avoids that entirely.
 *
 *
 * 2. HOW "FLUSH" WORKS
 *
 * flush() DOES NOT call FLUSHDB / FLUSHALL.
 *
 * Instead, flush() bumps a namespace version stored in MySQL via raw PDO.
 * Effective Redis keys include:
 *
 *     <db_name>:v<namespaceVersion>:<logicalKey>
 *
 * So a tenant cache rebuild simply increments the version and makes all old
 * keys unreachable for that tenant only.
 *
 * This preserves per-tenant isolation and avoids wiping the whole cache server.
 *
 *
 * 3. WHY NAMESPACE VERSION IS IN MYSQL
 *
 * The namespace version is control state, not cache data.
 *
 * It must not live in Redis itself because then a Redis primary/fallback drift
 * or failover could cause stale namespace state. We use MySQL (raw PDO) as the
 * source of truth.
 *
 * This code intentionally uses raw PDO and does NOT go through Settings or
 * go()->getCache(), to avoid recursion:
 *
 *     cache -> namespace version lookup -> cache -> recursion
 *
 *
 * 4. SERIALIZATION STRATEGY
 *
 * This application currently caches both arrays and full PHP objects.
 * Therefore this backend uses explicit serialize()/unserialize() so behavior is
 * stable across:
 *
 * - web requests (FPM / Apache)
 * - CLI
 * - Redis serializer settings
 *
 * We intentionally do NOT rely on Redis extension serializer options.
 *
 * On unserialize failure, this backend:
 * - logs the failure
 * - treats it as cache miss
 * - tries to delete the bad key best-effort
 *
 *
 * 5. MISS SEMANTICS
 *
 * Cache miss must return NULL exactly, not false.
 *
 * The application relies on strict null misses.
 *
 *
 * 6. FAIL-OPEN BEHAVIOR
 *
 * Redis failures must not crash application requests.
 *
 * If the current Redis server fails:
 * - log error
 * - switch to next configured fallback, if any
 * - retry in configured order
 *
 * If all servers fail:
 * - log error
 * - return null from get()
 * - continue request so app can rebuild from MySQL
 *
 * set()/delete()/flush() also log failures but do not throw.
 *
 *
 * 7. ORDERED PRIMARY / FALLBACK BEHAVIOR
 *
 * This is PER REQUEST, not global.
 *
 * A request starts on configured server #1 (primary).
 * If an operation fails there, this instance switches to server #2, then #3,
 * etc, in configured order.
 *
 * The rest of that request uses the first working fallback it found.
 *
 * The next PHP request starts on server #1 again.
 *
 * Important:
 * If these Redis servers are independent caches (not replicas), this is
 * availability failover, not coherent shared-cache HA. That is acceptable for
 * this cache because namespace versioning in MySQL is the correctness boundary,
 * but cache hit ratio may degrade during/after failover.
 *
 * Also:
 * "Current server unavailable at request start" is handled. A startup connection
 * failure is treated as backend unavailable, not as a normal cache miss, so
 * get() can still cascade to later configured servers.
 *
 *
 * 8. REQUEST-LOCAL MEMORY CACHE
 *
 * This backend keeps request-local memory cache behavior from the old cache
 * implementations:
 *
 * - if keepInMemory is enabled, values are memoized in $this->cache
 * - persist=false writes only to request memory, not Redis
 * - disableMemory() disables this request-local memoization
 *
 * Important:
 * flush() clears request-local memory immediately in the current request.
 *
 * For flush(true), this backend also disables further remote cache access for
 * the remainder of the request until __destruct() performs the namespace bump.
 * This avoids a stale-read window inside the same request.
 *
 *
 * 9. OLD MEMCACHED add() BUG IS FIXED
 *
 * The old Memcached backend used add() inside set(), which meant existing keys
 * were not overwritten. That is surprising and unsafe for metadata refresh.
 *
 * This Redis backend uses normal overwrite semantics:
 *
 *     set() overwrites existing values
 *
 * This is an intentional behavior correction.
 *
 *
 * 10. TTL POLICY
 *
 * Default TTL semantics are preserved:
 *
 * - ttl = 0 means no expiration
 * - explicit ttl > 0 is honored
 *
 * We are NOT introducing forced TTLs here. Namespace versioning is the
 * correctness mechanism. Cleanup of old generations can be added later by
 * background jobs if needed.
 *
 *
 * 11. NAMESPACE LOOKUP FAILURE POLICY
 *
 * If MySQL namespace version lookup fails, this backend does NOT fall back to a
 * guessed version such as 1. That could resurrect stale old cache generations.
 *
 * Instead, remote cache is treated as unavailable for the rest of the request.
 * The request remains fail-open and can rebuild from MySQL directly.
 *
 *
 * 12. FLUSH SIGNATURE COMPATIBILITY
 *
 * Existing code paths may call flush(true, false). The second argument is not
 * used by this backend, but is accepted for backward compatibility so existing
 * installer / upgrade paths do not break.
 *
 *
 * 13. CONFIG FORMAT
 *
 * Preferred config:
 *
 * 'cacheRedisServers' => [
 *     ['host' => 'redis1', 'port' => 6379, 'db' => 0, 'password' => '...'],
 *     ['host' => 'redis2'],
 *     ['host' => 'redis3']
 * ]
 *
 * The first entry is primary. Remaining entries are tried as fallbacks.
 *
 * Missing per-server fields inherit from:
 * - cacheRedisPort
 * - cacheRedisDb
 * - cacheRedisPassword
 *
 * Legacy compatibility is also supported:
 * - cacheRedisHost / cacheRedisPort / cacheRedisDb / cacheRedisPassword
 * - cacheRedisHostBak / cacheRedisPortBak / cacheRedisDbBak / cacheRedisPasswordBak
 *
 *
 * 14. PDO LIFECYCLE SAFETY
 *
 * Namespace version queries use raw PDO from go()->getDbConnection(), but this
 * class intentionally does NOT store PDO in an object property.
 *
 * Reason:
 * - this cache backend can run in long-lived loops (SSE / z-push / CLI)
 * - storing PDO on this object can keep MySQL sessions alive in SLEEP even
 *   after go()->getDbConnection()->disconnect() is called
 *
 * Therefore PDO is resolved per namespace operation and kept local to method
 * scope. Connection ownership remains with go()->getDbConnection().
 */
class Redis implements CacheInterface, AuthenticationCounterStore
{
	private const REMOTE_UNAVAILABLE = '__GO_REDIS_REMOTE_UNAVAILABLE__';
	private const DEFAULT_NAMESPACE_KEY = '__cache_nsver';

	/** Each operation is atomic across workers; expiry remains tied to the first counted attempt. */
	private const AUTHENTICATION_COUNTER_SCRIPT = <<<'LUA'
local action = ARGV[1]
local now = tonumber(ARGV[2])
local limit = tonumber(ARGV[3])
local window = tonumber(ARGV[4])
local values = redis.call('HMGET', KEYS[1], 'start', 'count', 'until')
local start = nil
local count = 0
local untilAt = 0
if values[1] or values[2] or values[3] then
    start = tonumber(values[1])
    count = tonumber(values[2])
    untilAt = tonumber(values[3])
    if not start or not count or not untilAt or start <= 0 or count < 0 or untilAt < 0
        or start ~= math.floor(start) or count ~= math.floor(count) or untilAt ~= math.floor(untilAt)
        or start > 9007199254740991 or count > 9007199254740991 or untilAt > 9007199254740991 then
        return redis.error_reply('Invalid authentication counter state')
    end
    if start + window <= now then
        redis.call('DEL', KEYS[1])
        start = nil
        count = 0
        untilAt = 0
    end
end
if action == 'reset' then
    redis.call('DEL', KEYS[1])
    return 1
end
if action ~= 'increment' and untilAt > now then
    return 0
end
if action == 'check' then
    return 1
end
start = start or now
count = count + 1
untilAt = 0
if count >= limit then
    untilAt = now + math.min(window, 30 * (2 ^ math.min(5, count - limit)))
end
redis.call('HMSET', KEYS[1], 'start', start, 'count', count, 'until', untilAt)
redis.call('EXPIREAT', KEYS[1], start + window)
return 1
LUA;

	/**
	 * Security counters bypass request memory and ordinary cache generations.
	 * Reuse the cache's configured failover; a fallback may have a fresh allowance.
	 */
	public function updateAuthenticationCounter(string $key, string $operation, int $limit, int $window): bool
	{
		if (!preg_match('/\A[a-f0-9]{64}\z/D', $key)
			|| !in_array($operation, ['check', 'increment', 'consume', 'reset'], true)
			|| $limit < 1 || $window < 1) {
			throw new \InvalidArgumentException('Invalid authentication counter operation');
		}
		$redisKey = $this->prefix . ':auth-throttle:v1:' . $key;
		while (true) {
			try {
				if (!$this->redis instanceof \Redis) {
					throw new RuntimeException('Authentication counter Redis connection is unavailable');
				}
				// An uncertain reply must not replay an increment on the same server.
				// Restore the existing option so ordinary cache behavior is unchanged.
				$redis = $this->redis;
				$retries = defined('Redis::OPT_MAX_RETRIES') ? $redis->getOption(\Redis::OPT_MAX_RETRIES) : null;
				if ($retries !== null) { $redis->setOption(\Redis::OPT_MAX_RETRIES, 0); }
				try {
					$redis->clearLastError();
					$result = $redis->eval(self::AUTHENTICATION_COUNTER_SCRIPT,
						[$redisKey, $operation, (string) time(), (string) $limit, (string) $window], 1);
					if ($result !== 0 && $result !== 1) {
						throw new RuntimeException('Authentication counter Redis operation failed: '
							. ($redis->getLastError() ?: 'unexpected reply'));
					}
					return $result === 1;
				} finally {
					if ($retries !== null) { $redis->setOption(\Redis::OPT_MAX_RETRIES, $retries); }
				}
			} catch (\Throwable $e) {
				if (!$this->switchToNextAvailableServer('authentication counter')) { throw $e; }
			}
		}
	}

	private $prefix;
	private $cache = [];

	/**
	 * Keep values in memory as long as the request lives. Disabled for SSE.
	 *
	 * @var bool
	 */
	private $keepInMemory = true;

	private $flushOnDestruct = false;

	/**
	 * Once true, remote cache access is bypassed for the remainder of the request.
	 *
	 * Used for:
	 * - deferred flush(true), to avoid stale reads before __destruct()
	 * - namespace version lookup failure, to fail open safely
	 *
	 * @var bool
	 */
	private $remoteDisabledForRequest = false;

	/**
	 * @var \Redis|null
	 */
	private $redis;

	/**
	 * Ordered Redis server definitions.
	 *
	 * Each item:
	 * [
	 *   'host' => string,
	 *   'port' => int,
	 *   'db' => int,
	 *   'password' => ?string
	 * ]
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $servers = [];

	/**
	 * Currently active server index within $servers.
	 *
	 * @var int|null
	 */
	private $activeServerIndex = null;

	private $activeHost;
	private $activePort;
	private $activeDb;

	/**
	 * Request-local memoization of the namespace version.
	 *
	 * @var int|null
	 */
	private $namespaceVersion;

	/**
	 * True once namespace version lookup failed in this request.
	 *
	 * @var bool
	 */
	private $namespaceLookupFailed = false;

	/**
	 * core/core moduleId, memoized per request.
	 *
	 * @var int|null
	 */
	private $namespaceCoreModuleId;

	/**
	 * Request-local memoization of namespace setting values.
	 *
	 * @var array<string,int>
	 */
	private $namespaceLocalCache = [];

	public function disableMemory(): void
	{
		$this->cache = [];
		$this->keepInMemory = false;
	}

	/**
	 * Remove all request-local memory except the given keys
	 *
	 * @param array $preserveKeys
	 * @return void
	 */
	public function freeMemory(array $preserveKeys = ['entity-types']): void
	{
		$this->cache = array_intersect_key($this->cache, array_flip($preserveKeys));
	}

	public function __construct()
	{
		$this->prefix = go()->getConfig()['db_name'];
		$this->servers = $this->buildServerListFromConfig();

		if (empty($this->servers)) {
			throw new ConfigurationException(
				"Redis cache requires either 'cacheRedisServers' or legacy 'cacheRedisHost' config"
			);
		}

		go()->debug(
			"Redis cache initialized for prefix '" . $this->prefix .
			"' with " . count($this->servers) . " configured server(s): " .
			json_encode($this->getConfiguredServersForLog()),
			1
		);

		$this->connectToServerIndex(0);
	}

	/**
	 * Build ordered server list from config.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function buildServerListFromConfig(): array
	{
		$config = go()->getConfig();

		$defaultPort = (int) ($config['cacheRedisPort'] ?? 6379);
		$defaultDb = (int) ($config['cacheRedisDb'] ?? 0);
		$defaultPassword = $config['cacheRedisPassword'] ?? null;

		$servers = [];

		if (isset($config['cacheRedisServers'])) {
			if (!is_array($config['cacheRedisServers'])) {
				throw new ConfigurationException("'cacheRedisServers' must be an array");
			}

			foreach ($config['cacheRedisServers'] as $idx => $server) {
				if (!is_array($server)) {
					throw new ConfigurationException("cacheRedisServers[$idx] must be an array");
				}

				$host = $server['host'] ?? null;
				if (!is_string($host) || $host === '') {
					throw new ConfigurationException("cacheRedisServers[$idx]['host'] is required");
				}

				$password = array_key_exists('password', $server)
					? $server['password']
					: $defaultPassword;

				if ($password !== null && !is_string($password)) {
					throw new ConfigurationException("cacheRedisServers[$idx]['password'] must be string or null");
				}

				$servers[] = [
					'host' => $host,
					'port' => (int) ($server['port'] ?? $defaultPort),
					'db' => (int) ($server['db'] ?? $defaultDb),
					'password' => $password
				];
			}

			return $servers;
		}

		if (!empty($config['cacheRedisHost'])) {
			$servers[] = [
				'host' => $config['cacheRedisHost'],
				'port' => (int) ($config['cacheRedisPort'] ?? 6379),
				'db' => (int) ($config['cacheRedisDb'] ?? 0),
				'password' => $config['cacheRedisPassword'] ?? null
			];
		}

		if (!empty($config['cacheRedisHostBak'])) {
			$legacyBakPassword = array_key_exists('cacheRedisPasswordBak', $config)
				? $config['cacheRedisPasswordBak']
				: ($config['cacheRedisPassword'] ?? null);

			$servers[] = [
				'host' => $config['cacheRedisHostBak'],
				'port' => (int) ($config['cacheRedisPortBak'] ?? ($config['cacheRedisPort'] ?? 6379)),
				'db' => (int) ($config['cacheRedisDbBak'] ?? ($config['cacheRedisDb'] ?? 0)),
				'password' => $legacyBakPassword
			];
		}

		return $servers;
	}

	/**
	 * Human-readable label for the current server.
	 */
	private function getActiveServerLabel(): string
	{
		if ($this->activeServerIndex === null) {
			return 'uninitialized';
		}

		return 'server #' . ($this->activeServerIndex + 1) .
			' (' . $this->activeHost . ':' . $this->activePort . ' db ' . $this->activeDb . ')';
	}

	/**
	 * Configured server list safe for logs.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function getConfiguredServersForLog(): array
	{
		$out = [];

		foreach ($this->servers as $idx => $server) {
			$out[] = [
				'index' => $idx,
				'host' => $server['host'],
				'port' => (int) $server['port'],
				'db' => (int) $server['db'],
				'hasPassword' => isset($server['password']) && $server['password'] !== ''
			];
		}

		return $out;
	}

	/**
	 * Establishes a Redis connection to the given configured server index.
	 *
	 * Particularities:
	 * - never throws to caller in normal request flow
	 * - logs on failure
	 * - uses short timeouts so fallback happens quickly
	 * - startup failure leaves $this->redis = null; callers must distinguish
	 *   "backend unavailable" from "cache miss"
	 * - optional AUTH is supported
	 */
	private function connectToServerIndex(int $index): void
	{
		if (!isset($this->servers[$index])) {
			$this->redis = null;
			$this->activeServerIndex = null;
			$this->activeHost = null;
			$this->activePort = null;
			$this->activeDb = null;
			return;
		}

		$server = $this->servers[$index];
		$host = $server['host'];
		$port = (int) $server['port'];
		$db = (int) $server['db'];
		$password = $server['password'] ?? null;

		try {
			go()->debug(
				"Redis cache attempting connection to server #" . ($index + 1) . " " .
				$host . ":" . $port . " db " . $db .
				" (hasPassword=" . ((isset($password) && $password !== '') ? 'yes' : 'no') . ")",
				1
			);

			$redis = new \Redis();

			$connectTimeout = (float) (go()->getConfig()['cacheRedisConnectTimeout'] ?? 0.2);
			$readTimeout = (float) (go()->getConfig()['cacheRedisReadTimeout'] ?? 0.2);
			$retryIntervalMs = (int) (go()->getConfig()['cacheRedisRetryIntervalMs'] ?? 100);

			$ok = $redis->connect($host, $port, $connectTimeout, null, $retryIntervalMs, $readTimeout);

			if (!$ok) {
				throw new RuntimeException("Redis connect() returned false");
			}

			if (isset($password) && $password !== '') {
				if (!$redis->auth($password)) {
					throw new RuntimeException("Redis AUTH failed");
				}
			}

			if ($db !== 0 && !$redis->select($db)) {
				throw new RuntimeException("Failed selecting Redis DB " . $db);
			}

			$this->redis = $redis;
			$this->activeServerIndex = $index;
			$this->activeHost = $host;
			$this->activePort = $port;
			$this->activeDb = $db;

			go()->debug(
				"Redis cache using server #" . ($index + 1) . " " . $host . ":" . $port . " db " . $db,
				1
			);
		} catch (\Throwable $e) {
			ErrorHandler::logException(
				$e,
				"Failed to initialize Redis cache connection for server #" . ($index + 1) .
				" " . $host . ":" . $port . " db " . $db
			);

			$this->redis = null;
			$this->activeServerIndex = $index;
			$this->activeHost = $host;
			$this->activePort = $port;
			$this->activeDb = $db;

			go()->debug(
				"Redis cache connection initialization failed for server #" . ($index + 1) .
				"; active label now " . $this->getActiveServerLabel(),
				1
			);
		}
	}

	/**
	 * Tries the next configured fallback server(s) in order.
	 *
	 * Returns true once a working connection is established.
	 * Returns false if no further configured server is available.
	 */
	private function switchToNextAvailableServer(string $action, ?string $logicalKey = null): bool
	{
		$currentIndex = $this->activeServerIndex ?? -1;
		$nextIndex = $currentIndex + 1;

		go()->debug(
			"Redis cache failover check for action '" . $action . "'" .
			($logicalKey !== null ? " key '" . $logicalKey . "'" : "") .
			": activeServerIndex=" . var_export($this->activeServerIndex, true) .
			", currentIndex=" . $currentIndex .
			", nextIndex=" . $nextIndex .
			", serverCount=" . count($this->servers) .
			", activeLabel=" . $this->getActiveServerLabel() .
			", configuredServers=" . json_encode($this->getConfiguredServersForLog()),
			1
		);

		if (!isset($this->servers[$nextIndex])) {
			go()->debug(
				"Redis cache has no fallback server at index " . $nextIndex .
				" for action '" . $action . "'" .
				($logicalKey !== null ? " key '" . $logicalKey . "'" : ""),
				1
			);
			return false;
		}

		$msg = "Redis cache " . $action . " failed on " . $this->getActiveServerLabel();

		if (isset($logicalKey)) {
			$msg .= " for key '" . $logicalKey . "'";
		}

		$msg .= "; trying fallback servers in configured order starting at index " . $nextIndex;

		go()->error($msg);

		for ($i = $nextIndex, $len = count($this->servers); $i < $len; $i++) {
			go()->debug(
				"Redis cache attempting fallback candidate index " . $i,
				1
			);

			$this->connectToServerIndex($i);

			if ($this->redis instanceof \Redis) {
				go()->error(
					"Redis cache switched to fallback server #" . ($i + 1) .
					" " . $this->activeHost . ":" . $this->activePort . " db " . $this->activeDb
				);
				return true;
			}
		}

		go()->debug(
			"Redis cache exhausted all fallback candidates for action '" . $action . "'" .
			($logicalKey !== null ? " key '" . $logicalKey . "'" : ""),
			1
		);

		return false;
	}

	/**
	 * Returns true if remote cache access is allowed in this request.
	 */
	private function isRemoteUsable(): bool
	{
		return !$this->remoteDisabledForRequest;
	}

	/**
	 * Resolves core/core module ID once and memoizes it.
	 */
	private function getNamespaceCoreModuleId(): int
	{
		if ($this->namespaceCoreModuleId !== null) {
			return $this->namespaceCoreModuleId;
		}

		$pdo = go()->getDbConnection()->getPDO();

		$stmt = $pdo->prepare(
			"SELECT `id` FROM `core_module` WHERE `package` = 'core' AND `name` = 'core' LIMIT 1"
		);
		$stmt->execute();
		$id = (int) $stmt->fetchColumn();
		$stmt->closeCursor();

		if ($id < 1) {
			throw new RuntimeException("core/core module not found in core_module");
		}

		$this->namespaceCoreModuleId = $id;

		go()->debug(
			"Redis cache namespace core module id resolved to " . $id,
			1
		);

		return $id;
	}

	/**
	 * Inserts default namespace row if missing.
	 */
	private function insertNamespaceVersionIfMissing(int $moduleId, string $key, int $version): void
	{
		$pdo = go()->getDbConnection()->getPDO();

		$stmt = $pdo->prepare(
			"INSERT INTO `core_setting` (`moduleId`, `name`, `value`)
			 VALUES (:moduleId, :name, :value)
			 ON DUPLICATE KEY UPDATE `value` = `value`"
		);

		$stmt->execute([
			':moduleId' => $moduleId,
			':name' => $key,
			':value' => (string) $version
		]);
		$stmt->closeCursor();

		go()->debug(
			"Redis cache namespace row ensured for moduleId " . $moduleId .
			" key '" . $key . "' with default version " . $version,
			1
		);
	}

	/**
	 * Returns current namespace version (creates row with version=1 if missing).
	 */
	private function loadNamespaceVersion(string $key = self::DEFAULT_NAMESPACE_KEY): int
	{
		if (isset($this->namespaceLocalCache[$key])) {
			return $this->namespaceLocalCache[$key];
		}

		$moduleId = $this->getNamespaceCoreModuleId();
		$pdo = go()->getDbConnection()->getPDO();

		$stmt = $pdo->prepare(
			"SELECT `value` FROM `core_setting` WHERE `moduleId` = :moduleId AND `name` = :name"
		);
		$stmt->execute([
			':moduleId' => $moduleId,
			':name' => $key
		]);

		$value = $stmt->fetchColumn();
		$stmt->closeCursor();

		if ($value === false) {
			$this->insertNamespaceVersionIfMissing($moduleId, $key, 1);
			$this->namespaceLocalCache[$key] = 1;

			go()->debug(
				"Redis cache namespace version for key '" . $key . "' was missing; initialized to 1",
				1
			);

			return 1;
		}

		$version = (int) $value;
		if ($version < 1) {
			$version = 1;
		}

		$this->namespaceLocalCache[$key] = $version;

		go()->debug(
			"Redis cache namespace version for key '" . $key . "' loaded from DB: " . $version,
			1
		);

		return $version;
	}

	/**
	 * Atomically increments namespace version and returns new value.
	 */
	private function incrementNamespaceVersion(string $key = self::DEFAULT_NAMESPACE_KEY): int
	{
		$moduleId = $this->getNamespaceCoreModuleId();
		$pdo = go()->getDbConnection()->getPDO();

		// A recovery flush may be the first cache operation. Advance beyond
		// the default generation (1), and set LAST_INSERT_ID on both paths.
		$sql = "
			INSERT INTO `core_setting` (`moduleId`, `name`, `value`)
			VALUES (:moduleId, :name, CAST(LAST_INSERT_ID(2) AS CHAR))
			ON DUPLICATE KEY UPDATE
				`value` = CAST(LAST_INSERT_ID(CAST(COALESCE(`value`, '0') AS UNSIGNED) + 1) AS CHAR)
		";

		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':moduleId' => $moduleId,
			':name' => $key
		]);
		$stmt->closeCursor();

		$newVersion = (int) $pdo->query("SELECT LAST_INSERT_ID()")->fetchColumn();

		if ($newVersion < 1) {
			throw new RuntimeException("Failed to bump namespace version");
		}

		$this->namespaceLocalCache[$key] = $newVersion;

		go()->debug(
			"Redis cache namespace version for key '" . $key . "' bumped to " . $newVersion,
			1
		);

		return $newVersion;
	}

	/**
	 * Returns the DB-backed namespace version for this tenant.
	 *
	 * Particularities:
	 * - uses raw PDO path to avoid cache recursion
	 * - memoized locally for this request
	 * - on failure, remote cache is disabled for this request
	 * - never guesses or rewinds namespace version
	 */
	private function getNamespaceVersion(): ?int
	{
		if ($this->namespaceLookupFailed || $this->remoteDisabledForRequest) {
			return null;
		}

		if (isset($this->namespaceVersion)) {
			return $this->namespaceVersion;
		}

		try {
			$this->namespaceVersion = $this->loadNamespaceVersion();
			return $this->namespaceVersion;
		} catch (\Throwable $e) {
			$this->namespaceLookupFailed = true;
			$this->remoteDisabledForRequest = true;

			ErrorHandler::logException(
				$e,
				"Failed reading cache namespace version; disabling remote cache for this request"
			);

			return null;
		}
	}

	/**
	 * Bumps the DB-backed namespace version for this tenant.
	 *
	 * Particularities:
	 * - this is the per-tenant "flush" operation
	 * - local request cache is cleared immediately
	 * - namespaceVersion memoization is refreshed to the new value
	 * - on success, remote cache is re-enabled for this request
	 * - on failure, request still continues fail-open
	 */
	private function bumpNamespaceVersion(): bool
	{
		try {
			$this->namespaceVersion = $this->incrementNamespaceVersion();
			$this->namespaceLookupFailed = false;
			$this->remoteDisabledForRequest = false;
			$this->cache = [];

			go()->debug(
				"Redis cache namespace bumped for prefix '" . $this->prefix .
				"' to version " . $this->namespaceVersion,
				1
			);

			return true;
		} catch (\Throwable $e) {
			ErrorHandler::logException(
				$e,
				"Failed bumping cache namespace version for prefix '" . $this->prefix . "'"
			);
			$this->cache = [];
			unset($this->namespaceVersion);
			return false;
		}
	}

	/**
	 * Builds the effective remote key.
	 *
	 * Key format:
	 *   <db_name>:v<namespaceVersion>:<logicalKey>
	 *
	 * Returns null if remote cache is unavailable in this request.
	 */
	private function buildRemoteKey(string $logicalKey): ?string
	{
		$version = $this->getNamespaceVersion();
		if ($version === null) {
			return null;
		}

		return $this->prefix . ':v' . $version . ':' . $logicalKey;
	}

	/**
	 * Explicit serialization to preserve PHP arrays / objects / scalars exactly.
	 */
	private function encodeValue($value): string
	{
		return serialize($value);
	}

	/**
	 * Explicit unserialization.
	 *
	 * Particularities:
	 * - any corruption / class mismatch / invalid payload is treated as miss
	 * - bad key is deleted best-effort
	 */
	private function decodeValue(string $payload, string $logicalKey)
	{
		try {
			$value = @unserialize($payload, ['allowed_classes' => true]); // classes must be allowed because we use this in our ORM;

			if ($value === false && $payload !== serialize(false)) {
				throw new RuntimeException("unserialize() returned false for non-false payload");
			}

			return $value;
		} catch (\Throwable $e) {
			ErrorHandler::logException(
				$e,
				"Failed to unserialize Redis cache value for key '" . $logicalKey . "'"
			);

			try {
				$this->deleteRemoteKey($logicalKey);
			} catch (\Throwable $ignored) {
			}

			return null;
		}
	}

	/**
	 * Deletes the effective remote key for the current namespace version.
	 */
	private function deleteRemoteKey(string $logicalKey): void
	{
		if (!$this->redis instanceof \Redis || !$this->isRemoteUsable()) {
			return;
		}

		$remoteKey = $this->buildRemoteKey($logicalKey);
		if ($remoteKey === null) {
			return;
		}

		$this->redis->del($remoteKey);
	}

	/**
	 * Store any value in the cache.
	 *
	 * Particularities:
	 * - persist=false only stores request-local, not Redis
	 * - request-local memoization still happens when enabled
	 * - overwrite semantics are intentional (fixes old Memcached add() behavior)
	 * - Redis failure logs and retries on fallbacks in order, but never throws
	 * - if remote cache is disabled for this request, only request-local cache is updated
	 */
	public function set(string $key, $value, bool $persist = true, int $ttl = 0)
	{
		if ($this->keepInMemory) {
			$this->cache[$key] = $value;
		}

		if (!$persist || !$this->isRemoteUsable()) {
			return true;
		}

		$payload = $this->encodeValue($value);
		$ok = false;

		try {
			$ok = $this->writeRemote($key, $payload, $ttl);

			while (!$ok && $this->switchToNextAvailableServer('set', $key)) {
				$ok = $this->writeRemote($key, $payload, $ttl);
			}

			if (!$ok) {
				go()->error(
					"Redis cache set failed on all configured servers for key '" . $key . "'"
				);
			}
		} catch (\Throwable $e) {
			ErrorHandler::logException(
				$e,
				"Exception during Redis cache set for key '" . $key . "' on " . $this->getActiveServerLabel()
			);

			while ($this->switchToNextAvailableServer('set', $key)) {
				try {
					$ok = $this->writeRemote($key, $payload, $ttl);
					if ($ok) {
						return true;
					}
				} catch (\Throwable $e2) {
					ErrorHandler::logException(
						$e2,
						"Exception during Redis cache set retries for key '" . $key . "' on " . $this->getActiveServerLabel()
					);
				}
			}

			if (!$ok) {
				go()->error(
					"Redis cache set failed on all configured fallback servers for key '" . $key . "'"
				);
			}
		}

		return true;
	}

	/**
	 * Performs the actual Redis write.
	 *
	 * Return values:
	 * - true  => write attempted and succeeded
	 * - false => backend unavailable or write failed
	 */
	private function writeRemote(string $logicalKey, string $payload, int $ttl): bool
	{
		if (!$this->redis instanceof \Redis || !$this->isRemoteUsable()) {
			go()->debug(
				"Redis cache writeRemote returning false for key '" . $logicalKey .
				"' because redis is not connected or remote is unusable " .
				"(redisConnected=" . (($this->redis instanceof \Redis) ? 'true' : 'false') .
				", remoteUsable=" . ($this->isRemoteUsable() ? 'true' : 'false') . ")",
				1
			);
			return false;
		}

		$remoteKey = $this->buildRemoteKey($logicalKey);
		if ($remoteKey === null) {
			go()->debug(
				"Redis cache writeRemote returning false for key '" . $logicalKey .
				"' because buildRemoteKey() returned null",
				1
			);
			return false;
		}

		go()->debug(
			"Redis cache writeRemote attempting key '" . $logicalKey .
			"' as remote key '" . $remoteKey . "' on " . $this->getActiveServerLabel() .
			" ttl=" . $ttl,
			1
		);

		if ($ttl > 0) {
			$result = (bool) $this->redis->set($remoteKey, $payload, $ttl);
		} else {
			$result = (bool) $this->redis->set($remoteKey, $payload);
		}

		go()->debug(
			"Redis cache writeRemote result for key '" . $logicalKey .
			"' on " . $this->getActiveServerLabel() . ": " .
			($result ? 'success' : 'failure'),
			1
		);

		return $result;
	}

	/**
	 * Get a value from the cache.
	 *
	 * Particularities:
	 * - request-local memory checked first
	 * - strict miss => null
	 * - backend unavailable is distinguished from cache miss so fallback failover can occur
	 * - falsey values are memoized correctly in request-local cache
	 */
	public function get(string $key)
	{
		if (array_key_exists($key, $this->cache)) {
			return $this->cache[$key];
		}

		if (!$this->isRemoteUsable()) {
			return null;
		}

		try {
			$payload = $this->readRemote($key);

			while ($payload === self::REMOTE_UNAVAILABLE && $this->switchToNextAvailableServer('get', $key)) {
				$payload = $this->readRemote($key);
			}

			if ($payload === self::REMOTE_UNAVAILABLE || $payload === null) {
				return null;
			}

			$value = $this->decodeValue($payload, $key);

			if ($this->keepInMemory) {
				$this->cache[$key] = $value;
			}

			return $value;
		} catch (\Throwable $e) {
			ErrorHandler::logException(
				$e,
				"Exception during Redis cache get for key '" . $key . "' on " . $this->getActiveServerLabel()
			);

			while ($this->switchToNextAvailableServer('get', $key)) {
				try {
					$payload = $this->readRemote($key);

					if ($payload === self::REMOTE_UNAVAILABLE) {
						continue;
					}

					if ($payload === null) {
						return null;
					}

					$value = $this->decodeValue($payload, $key);

					if ($this->keepInMemory) {
						$this->cache[$key] = $value;
					}

					return $value;
				} catch (\Throwable $e2) {
					ErrorHandler::logException(
						$e2,
						"Exception during Redis cache get retries for key '" . $key . "' on " . $this->getActiveServerLabel()
					);
				}
			}

			return null;
		}
	}

	/**
	 * Reads payload from Redis and returns:
	 * - string payload on hit
	 * - null on miss
	 * - self::REMOTE_UNAVAILABLE when backend cannot be used
	 *
	 * This distinction is important so get() can trigger failover when
	 * the current server was unreachable at request start.
	 */
	private function readRemote(string $logicalKey)
	{
		if (!$this->isRemoteUsable() || !($this->redis instanceof \Redis)) {
			go()->debug(
				"Redis cache readRemote returning REMOTE_UNAVAILABLE for key '" . $logicalKey .
				"' because redis is not connected or remote is unusable " .
				"(redisConnected=" . (($this->redis instanceof \Redis) ? 'true' : 'false') .
				", remoteUsable=" . ($this->isRemoteUsable() ? 'true' : 'false') . ")",
				1
			);
			return self::REMOTE_UNAVAILABLE;
		}

		$remoteKey = $this->buildRemoteKey($logicalKey);
		if ($remoteKey === null) {
			go()->debug(
				"Redis cache readRemote returning REMOTE_UNAVAILABLE for key '" . $logicalKey .
				"' because buildRemoteKey() returned null",
				1
			);
			return self::REMOTE_UNAVAILABLE;
		}

		$payload = $this->redis->get($remoteKey);

		if ($payload === false) {
			return null;
		}

		return (string) $payload;
	}

	/**
	 * Delete a value from the cache.
	 *
	 * Particularities:
	 * - request-local copy removed first
	 * - remote delete is best-effort
	 * - on failure, retries on fallbacks in order
	 * - if remote cache is disabled for this request, local removal only
	 */
	public function delete(string $key)
	{
		unset($this->cache[$key]);

		if (!$this->isRemoteUsable()) {
			return true;
		}

		$ok = false;

		try {
			$ok = $this->deleteRemote($key);

			while (!$ok && $this->switchToNextAvailableServer('delete', $key)) {
				$ok = $this->deleteRemote($key);
			}

			if (!$ok) {
				go()->error(
					"Redis cache delete failed on all configured servers for key '" . $key . "'"
				);
			}
		} catch (\Throwable $e) {
			ErrorHandler::logException(
				$e,
				"Exception during Redis cache delete for key '" . $key . "' on " . $this->getActiveServerLabel()
			);

			while ($this->switchToNextAvailableServer('delete', $key)) {
				try {
					$ok = $this->deleteRemote($key);
					if ($ok) {
						return true;
					}
				} catch (\Throwable $e2) {
					ErrorHandler::logException(
						$e2,
						"Exception during Redis cache delete retries for key '" . $key . "' on " . $this->getActiveServerLabel()
					);
				}
			}

			if (!$ok) {
				go()->error(
					"Redis cache delete failed on all configured fallback servers for key '" . $key . "'"
				);
			}
		}

		return true;
	}

	private function deleteRemote(string $logicalKey): bool
	{
		if (!$this->redis instanceof \Redis || !$this->isRemoteUsable()) {
			go()->debug(
				"Redis cache deleteRemote returning false for key '" . $logicalKey .
				"' because redis is not connected or remote is unusable " .
				"(redisConnected=" . (($this->redis instanceof \Redis) ? 'true' : 'false') .
				", remoteUsable=" . ($this->isRemoteUsable() ? 'true' : 'false') . ")",
				1
			);
			return false;
		}

		$remoteKey = $this->buildRemoteKey($logicalKey);
		if ($remoteKey === null) {
			go()->debug(
				"Redis cache deleteRemote returning false for key '" . $logicalKey .
				"' because buildRemoteKey() returned null",
				1
			);
			return false;
		}

		go()->debug(
			"Redis cache deleteRemote attempting key '" . $logicalKey .
			"' as remote key '" . $remoteKey . "' on " . $this->getActiveServerLabel(),
			1
		);

		$this->redis->del($remoteKey);
		return true;
	}

	/**
	 * Flush all values for this tenant.
	 *
	 * Particularities:
	 * - onDestruct=true defers namespace bump until request end
	 * - but remote cache is disabled immediately for remainder of request,
	 *   preventing stale reads before destructor bump
	 * - immediate flush clears request-local memory now and bumps namespace
	 * - on successful immediate flush, remote cache is re-enabled
	 * - accepts an ignored second argument for compatibility with older callsites
	 * - no Redis FLUSHDB / FLUSHALL
	 * - no cross-tenant effect
	 */
	public function flush(bool $onDestruct = true, bool $legacyIgnored = false)
	{
		$this->cache = [];
		unset($this->namespaceVersion);

		if ($onDestruct) {
			$this->flushOnDestruct = true;
			$this->remoteDisabledForRequest = true;
			return true;
		}

		$this->flushOnDestruct = false;
		return $this->bumpNamespaceVersion();
	}

	public function __destruct()
	{
		if ($this->flushOnDestruct) {
			$this->flushOnDestruct = false;
			$this->bumpNamespaceVersion();
		}
	}

	public static function isSupported(): bool
	{
		return extension_loaded('redis');
	}
}
