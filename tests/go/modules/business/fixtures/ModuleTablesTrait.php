<?php
namespace go\modules\business;

/**
 * Licensed modules can't be installed in the tests because there's no license. This creates the tables and the module
 * record of such a module without the license check when it's not installed yet, and removes them again afterwards.
 */
trait ModuleTablesTrait
{
	/** @var string[] Tables created by installModuleTables() */
	private array $createdModuleTables = [];
	/** @var int[] Ids of module records created by installModuleTables() */
	private array $createdModuleIds = [];
	/** @var array[] [package, name] of the modules created by installModuleTables() */
	private array $createdModuleNames = [];

	/**
	 * Create the tables and module record of a module that isn't installed
	 *
	 * @param string|null $package The package, null for a legacy module
	 * @param string $name The module name
	 * @param string $installSql Path of the install.sql file of the module
	 * @param string[] $onlyTables Run only the CREATE TABLE statements of these tables
	 */
	private function installModuleTables(?string $package, string $name, string $installSql, array $onlyTables = []): void
	{
		$exists = go()->getDbConnection()->selectSingleValue('id')->from('core_module')
			->where(['package' => $package, 'name' => $name])->single();
		if ($exists) {
			return;
		}

		if (!is_file($installSql)) {
			$this->markTestSkipped('The ' . $name . ' module is not available');
		}

		go()->getDbConnection()->insert('core_module', ['name' => $name, 'package' => $package, 'version' => 0, 'enabled' => 1])->execute();
		$this->createdModuleIds[] = (int) go()->getDbConnection()->getPDO()->lastInsertId();
		$this->createdModuleNames[] = [$package, $name];

		$this->createTables($installSql, $onlyTables);
		$this->clearModuleCaches($package, $name);
	}

	/**
	 * Create tables of an install.sql file that don't exist yet, without a module record
	 *
	 * @param string $installSql
	 * @param string[] $onlyTables Run only the CREATE TABLE statements of these tables
	 */
	private function createTables(string $installSql, array $onlyTables = []): void
	{
		$tables = $this->findAllTables();
		if (!empty($onlyTables) && empty(array_diff($onlyTables, $tables))) {
			return;
		}

		try {
			foreach (\GO\Base\Util\SQL::getSqlQueries($installSql) as $query) {
				if (!empty($onlyTables) && !preg_match('/^\s*create\s+table\s+(if\s+not\s+exists\s+)?`?(' . implode('|', array_map('preg_quote', $onlyTables)) . ')`?\s*\(/i', $query)) {
					continue;
				}
				go()->getDbConnection()->exec($query);
			}
		} finally {
			$this->createdModuleTables = array_merge($this->createdModuleTables, array_diff($this->findAllTables(), $tables));
		}
	}

	/**
	 * Drop the tables and module records created by installModuleTables() and createTables()
	 */
	private function uninstallModuleTables(): void
	{
		$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
		try {
			foreach (array_reverse($this->createdModuleTables) as $table) {
				go()->getDbConnection()->exec('DROP TABLE IF EXISTS `' . $table . '`');
			}
		} finally {
			if ($oldFKchecks) {
				go()->getDbConnection()->foreignKeyChecks(true);
			}
		}
		$this->createdModuleTables = [];

		if (!empty($this->createdModuleIds)) {
			go()->getDbConnection()->delete('core_email_template', ['moduleId' => $this->createdModuleIds])->execute();
			go()->getDbConnection()->delete('core_module', ['id' => $this->createdModuleIds])->execute();
			$this->createdModuleIds = [];
		}
		foreach ($this->createdModuleNames as [$package, $name]) {
			$this->clearModuleCaches($package, $name);
		}
		$this->createdModuleNames = [];
		$this->clearModuleCaches();
	}

	private function clearModuleCaches(?string $package = null, ?string $name = null): void
	{
		go()->getCache()->delete('entity-types');
		if (isset($name)) {
			go()->getCache()->delete('module-' . ($package ?? 'legacy') . '/' . $name);
		}
		\go\core\orm\Entity::clearCache();
	}

	private function findAllTables(): array
	{
		return go()->getDbConnection()->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
	}
}
