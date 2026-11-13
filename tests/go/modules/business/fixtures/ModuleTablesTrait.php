<?php
namespace go\modules\business;

/**
 * Licensed modules can't be installed in the tests because there's no license. This creates the tables and the module
 * record of such a module without the license check when it's not installed yet, and removes them again afterwards.
 *
 * The methods are static so a test class can create the tables once in setUpBeforeClass() and drop them in
 * tearDownAfterClass(), instead of for every test in setUp() and tearDown().
 */
trait ModuleTablesTrait
{
	/** @var string[] Tables created by installModuleTables() */
	private static array $createdModuleTables = [];
	/** @var int[] Ids of module records created by installModuleTables() */
	private static array $createdModuleIds = [];
	/** @var array[] [package, name] of the modules created by installModuleTables() */
	private static array $createdModuleNames = [];

	/**
	 * Create the tables and module record of a module that isn't installed
	 *
	 * @param string|null $package The package, null for a legacy module
	 * @param string $name The module name
	 * @param string $installSql Path of the install.sql file of the module
	 * @param string[] $onlyTables Run only the CREATE TABLE statements of these tables
	 */
	private static function installModuleTables(?string $package, string $name, string $installSql, array $onlyTables = []): void
	{
		$exists = go()->getDbConnection()->selectSingleValue('id')->from('core_module')
			->where(['package' => $package, 'name' => $name])->single();
		if ($exists) {
			return;
		}

		if (!is_file($installSql)) {
			self::markTestSkipped('The ' . $name . ' module is not available');
		}

		go()->getDbConnection()->insert('core_module', ['name' => $name, 'package' => $package, 'version' => 0, 'enabled' => 1])->execute();
		self::$createdModuleIds[] = (int) go()->getDbConnection()->getPDO()->lastInsertId();
		self::$createdModuleNames[] = [$package, $name];

		try {
			self::createTables($installSql, $onlyTables);
		} catch (\Throwable $e) {
			// tearDown() isn't called when setUp() fails
			self::uninstallModuleTables();
			throw $e;
		}
		self::clearModuleCaches($package, $name);
	}

	/**
	 * Create tables of an install.sql file that don't exist yet, without a module record
	 *
	 * @param string $installSql
	 * @param string[] $onlyTables Run only the CREATE TABLE statements of these tables
	 */
	private static function createTables(string $installSql, array $onlyTables = []): void
	{
		$tables = self::findAllTables();
		if (!empty($onlyTables) && empty(array_diff($onlyTables, $tables))) {
			return;
		}

		// install.sql files may add foreign keys to tables that are created further on
		$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
		try {
			foreach (\GO\Base\Util\SQL::getSqlQueries($installSql) as $query) {
				if (!empty($onlyTables) && !preg_match('/^\s*create\s+table\s+(if\s+not\s+exists\s+)?`?(' . implode('|', array_map('preg_quote', $onlyTables)) . ')`?\s*\(/i', $query)) {
					continue;
				}
				go()->getDbConnection()->exec($query);
			}
		} finally {
			if ($oldFKchecks) {
				go()->getDbConnection()->foreignKeyChecks(true);
			}
			self::$createdModuleTables = array_merge(self::$createdModuleTables, array_diff(self::findAllTables(), $tables));
		}
	}

	/**
	 * Drop the tables and module records created by installModuleTables() and createTables()
	 */
	private static function uninstallModuleTables(): void
	{
		$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
		try {
			foreach (array_reverse(self::$createdModuleTables) as $table) {
				go()->getDbConnection()->exec('DROP TABLE IF EXISTS `' . $table . '`');
			}
		} finally {
			if ($oldFKchecks) {
				go()->getDbConnection()->foreignKeyChecks(true);
			}
		}
		self::$createdModuleTables = [];

		if (!empty(self::$createdModuleIds)) {
			go()->getDbConnection()->delete('core_email_template', ['moduleId' => self::$createdModuleIds])->execute();
			go()->getDbConnection()->delete('core_module', ['id' => self::$createdModuleIds])->execute();
			self::$createdModuleIds = [];
		}
		foreach (self::$createdModuleNames as [$package, $name]) {
			self::clearModuleCaches($package, $name);
		}
		self::$createdModuleNames = [];
		self::clearModuleCaches();
	}

	private static function clearModuleCaches(?string $package = null, ?string $name = null): void
	{
		go()->getCache()->delete('entity-types');
		// Listeners and entity references of the module would otherwise still be used after it's gone
		go()->getCache()->flush(false);
		\go\core\event\Listeners::get()->clear();
		if (isset($name)) {
			go()->getCache()->delete('module-' . ($package ?? 'legacy') . '/' . $name);
		}
		\go\core\orm\Entity::clearCache();
	}

	private static function findAllTables(): array
	{
		return go()->getDbConnection()->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
	}
}
