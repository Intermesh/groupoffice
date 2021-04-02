<?php

namespace go\core;

use Exception;
use GO;
use go\core\data\Model;
use go\core\db\Query;
use go\core\exception\Forbidden;
use go\core\Module;

/**
 * Settings model 
 * 
 * Any module can implement getSettings() and return a model that extends this
 * abstract class to store settings. All properties are automatically saved and
 * loaded from the "core_setting" table.
 * 
 * @see Module::getSettings()
 */
abstract class Settings extends Model {

  private static $instance = [];
	/**
	 * 
	 * @return static
	 */
	public static function get() {
    $cls = static::class;

	  if(!isset(self::$instance[$cls])) {
      $instance = static::dbIsReady() ? go()->getCache()->get($cls) : null;
      if ($instance !== null) {
        self::$instance[$cls] = $instance;
        return $instance;
      }

      $instance = new static;

		  if(static::dbIsReady()) {
			  go()->getCache()->set($cls, $instance);
		  }
      self::$instance[$cls] = $instance;
    }

		return self::$instance[$cls];
	}

	public static function flushCache() {
		self::$instance = [];
	}

	protected function getModuleId() {
		$moduleId = (new Query)
			->selectSingleValue('id')
			->from('core_module')
			->where([
					'name' => $this->getModuleName(), 
					'package' => $this->getModulePackageName()])
			->execute()
			->fetch();
		
		if(!$moduleId) {
			throw new \Exception ("Could not find module " .  $this->getModuleName() . "/" . $this->getModulePackageName());
		}
		
		return $moduleId;
	}
	
	protected function getModuleName() {
		return explode("\\", static::class)[3];
	}
	
	protected function getModulePackageName() {
		return explode("\\", static::class)[2];
	}
	
	private $oldData;


	private static function dbIsReady() {
		$ready = go()->getCache()->get('has_table_core_setting');
		if($ready) {
			return true;
		}

		try {
			$ready = go()->getDatabase()->hasTable('core_setting');
			if($ready) {
				go()->getCache()->set('has_table_core_setting', true);
			}
			return $ready;
		}catch(Exception $e) {
			go()->debug($e);
		}

		return false;
	}
	
	/**
	 * Constructor
	 * 
	 * @param int $moduleId If null is given the "core" module is used.
	 */
	protected function __construct() {

		$props = array_keys($this->getSettingProperties());	
		
		$record = array_filter($this->loadFromConfigFile(), function($key) use ($props) { return in_array($key, $props);}, ARRAY_FILTER_USE_KEY);
		$this->readOnlyKeys = array_keys($record);
		
		$this->setValues($record);


		if(static::dbIsReady()) {
			$selectProps = array_diff($props, $this->readOnlyKeys);

			if (!empty($selectProps)) {
				$stmt = (new Query)
					->select('name, value')
					->from('core_setting')
					->where([
						'moduleId' => $this->getModuleId(),
						'name' => $selectProps
					])
					->execute();

				while ($record = $stmt->fetch()) {
					$this->{$record['name']} = $record['value'];
				}
			}

		}
		
		$this->oldData = (array) $this;
	}
	
	private function loadFromConfigFile() {
		$config = go()->getConfig();
		
		$pkgName = $this->getModulePackageName();
		
		
		if(!isset($config[$pkgName])) {
			return [];
		}
		
		if($pkgName == "core") {
			$c = $config[$pkgName];
		} else
		{
			$modName = $this->getModuleName();

			if(!isset($config[$pkgName][$modName])) {
				return [];
			}
			$c = $config[$pkgName][$modName];
		}
		
		return $c;		
	}
	
	
	private $readOnlyKeys = [];
	
	public function getReadOnlyKeys() {
		return $this->readOnlyKeys;
	}
	
//	public function __destruct() {
//		$this->save();
//	}
	
	private function getSettingProperties() {
		$props =  array_filter(get_object_vars($this), function($key) {
			return $key !== 'oldData' && $key !== 'readOnlyKeys';
		}, ARRAY_FILTER_USE_KEY);		
		
		return $props;
	}

	protected function isModified($name) {
		return !array_key_exists($name, $this->oldData) && isset($this->$name) || $this->$name != $this->oldData[$name];
	}
	
	public function save() {

		foreach($this->getSettingProperties() as $name => $value) {
			if(!array_key_exists($name, $this->oldData) || $value != $this->oldData[$name]) {
				if(in_array($name, $this->readOnlyKeys)) {
					throw new Forbidden(static::class . ':' . $name . " can't be changed because it's defined in the configuration file on the server.");
				}
				
				$this->update($name, $value);
			}
		}

		$this->oldData = (array) $this;

		go()->getCache()->set(static::class, $this);
		
		return true;
	}
	
	private function update($name, $value) {
		
		$moduleId = $this->getModuleId();

		if(!$moduleId) {
			throw new \Exception("Could not find module for settings model ". static::class);
		}
		
		if (!App::get()->getDbConnection()->replace('core_setting', [
								'moduleId' => $moduleId,
								'name' => $name,
								'value' => $value
						])->execute()) {
			throw new Exception("Failed to set setting!");
		}
	}
}
