<?php

namespace go\core;

use Exception;
use go\core\db\Query;

/**
 * Settings model that can be used for the core and modules to store any string 
 * setting.
 */
abstract class Settings extends data\Model {
	
	use SingletonTrait;

	protected function getModuleId() {
		return (new Query)
			->selectSingleValue('id')
			->from('core_module')
			->where(['name' => $this->getModuleName(), 'package' => $this->getModulePackageName()])
			->execute()
			->fetch();
	}
	
	protected function getModuleName() {
		return explode("\\", static::class)[3];
	}
	
	protected function getModulePackageName() {
		return explode("\\", static::class)[2];
	}
	
	private $oldData;
	
	/**
	 * Constructor
	 * 
	 * @param int $moduleId If null is given the "core" module is used.
	 */
	protected function __construct() {
		
		if(GO()->getInstaller()->isInProgress()) {
			$this->oldData = [];
			return;
		}
		
		$props = array_keys($this->getSettingProperties());
		if(!empty($props)) {
			$stmt = (new Query)
							->select('name, value')
							->from('core_setting')
							->where([
									'moduleId' => $this->getModuleId(), 
									'name' => $props
									])
							->execute();
			
			while($record = $stmt->fetch()) {
				$this->{$record['name']} = $record['value'];
			}
		}
		
		$this->oldData = (array) $this;
	}
	
//	public function __destruct() {
//		$this->save();
//	}
	
	private function getSettingProperties() {
		$props =  array_filter(get_object_vars($this), function($key) {
			return $key !== 'oldData';
		}, ARRAY_FILTER_USE_KEY);		
		
		return $props;
	}
	
	public function save() {
		$new = (array) $this;
		
		foreach($this->getSettingProperties() as $name => $value) {
			
			if(!array_key_exists($name, $this->oldData) || $value != $this->oldData[$name]) {
				$this->update($name, $value);
			}
		}
		
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
