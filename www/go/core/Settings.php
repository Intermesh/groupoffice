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
		
			$stmt = (new Query)
							->select('name, value')
							->from('core_setting')
							->where(['moduleId' => $this->getModuleId()])
							->execute();
			
			while($record = $stmt->fetch()) {
				$this->{$record['name']} = $record['value'];
			}
			
			$this->oldData = (array) $this;
	}
	
//	public function __destruct() {
//		$this->save();
//	}
	
	public function save() {
		$new = (array) $this;
		
		foreach(get_object_vars($this) as $name => $value) {
			
			if($name == 'oldData') {
				continue;
			}
			
			if(!array_key_exists($name, $this->oldData) || $value != $this->oldData[$name]) {
				$this->update($name, $value);
			}
		}
		
		return true;
	}
	
	private function update($name, $value) {
		
		
		if (!App::get()->getDbConnection()->replace('core_setting', [
								'moduleId' => $this->getModuleId(),
								'name' => $name,
								'value' => $value
						])->execute()) {
			throw new Exception("Failed to set setting!");
		}
	}
}
