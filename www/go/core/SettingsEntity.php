<?php

namespace go\core;

use Exception;
use go\core\data\Model;
use go\core\db\Query;
use go\core\exception\Forbidden;
use go\core\orm\Entity;

/**
 * Settings model
 *
 * Any module can implement getSettings() and return a model that extends this
 * abstract class to store settings. All properties are automatically saved and
 * loaded from the "core_setting" table.
 *
 * @see Module::getSettings()
 */
abstract class SettingsEntity extends Entity {

	private static $instance = [];

	public static function get() {

		$cls = static::class;

		if(!isset(self::$instance[$cls])) {
			if(static::getMapping()->getPrimaryTable()) {
				self::$instance[$cls] = static::find()->single() ?? new static;
			} else {
				self::$instance[$cls] = new static;
			}

		}
		return static::$instance[$cls];
	}

	protected $readOnlyKeys;

	protected function init()
	{
		$config = self::loadPropertiesFromConfigFile();
		$this->readOnlyKeys = array_keys($config);
		$this->setValues($config);

		parent::init();
	}

	/** @noinspection PhpUnused */
	public function getReadOnlyKeys(): array
	{
		return $this->readOnlyKeys;
	}

}