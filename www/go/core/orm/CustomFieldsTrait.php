<?php
namespace go\core\orm;

use go\core\App;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\validate\ErrorCode;
use go\modules\core\customfields\model\Field;
use PDOException;

/**
 * Entities can use this trait to enable a customFields property that can be 
 * extended by the user.
 * 
 * @property array $customFields 
 */
trait CustomFieldsTrait {
	
	/**
	 * Holds the custom fields record data
	 * @var array
	 */
	private $customFieldsData;
	private $customFieldsModified = false;
	
	/**
	 * Get all custom fields data for an entity
	 * 
	 * @return array
	 */
	public function getCustomFields() {
		if(!isset($this->customFieldsData)) {
			$record = (new Query())
							->select('*')
							->from($this->customFieldsTableName(), 'cf')
							->where(['id' => $this->id])->single();
							
			if($record) {			
				
				$columns = Table::getInstance(static::customFieldsTableName())->getColumns();		
				foreach($columns as $name => $column) {					
					$record[$name] = $column->castFromDb($record[$name]);					
				}				
				
				foreach(self::getCustomFieldModels() as $field) {
					$record[$field->databaseName] = $field->getDataType()->dbToApi(isset($record[$field->databaseName]) ? $record[$field->databaseName] : null, $record);			
				}
				
				$this->customFieldsData = $record;
				
			} else
			{
				$this->customFieldsData = [];
			}
		}
		
		return $this->customFieldsData;
	}
	
	//for legacy modules
	public function setCustomFieldsJSON($json) {
		$data = json_decode($json, true);
		$this->setCustomFields($data);
	}
	
	/**
	 * Set custom field data
	 * @param array $data
	 */
	public function setCustomFields($data) {		
		$this->customFieldsData = array_merge($this->getCustomFields(), $this->normalizeCustomFieldsInput($data));		
		
		$this->customFieldsModified = true;
	}
	
	private static $customFields;
	
	/**
	 * Get all custom fields for this entity
	 * 
	 * @return Field
	 */
	public static function getCustomFieldModels() {
		if(!isset(self::$customFields)) {
			self::$customFields = Field::find()
						->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId')
						->where(['fs.entityId' => static::getType()->getId()])->all();
		}
		
		return self::$customFields;
	}
	
	
	private function normalizeCustomFieldsInput($data) {
		$columns = Table::getInstance(static::customFieldsTableName())->getColumns();		
		foreach($columns as $name => $column) {
			if(array_key_exists($name, $data)) {
				$data[$name] = $column->normalizeInput($data[$name]);
			}
		}
			
		foreach(self::getCustomFieldModels() as $field) {	
			//if client didn't post value then skip it
			if(array_key_exists($field->databaseName, $data)) {
				$data[$field->databaseName] = $field->getDataType()->apiToDb(isset($data[$field->databaseName]) ? $data[$field->databaseName] : null,  $data);			
			}
		}
		
		return $data;
	}
	
	/**
	 * Saves custom fields to the database. Is called by Entity::internalSave()
	 * 
	 * @return boolean
	 * @throws PDOException
	 */
	protected function saveCustomFields() {
		if(!$this->customFieldsModified) {
			return true;
		}
		
		try {			
			$record = $this->customFieldsData;			
			
			foreach(self::getCustomFieldModels() as $field) {
				if(!$field->getDataType()->beforeSave(isset($record[$field->databaseName]) ? $record[$field->databaseName] : null, $record)) {
					return false;
				}
			}
			
			if(!isset($record['id'])) {
				if(!empty($record)) {			
					$record['id'] = $this->id;	
					if(!App::get()
									->getDbConnection()
									->insert($this->customFieldsTableName(), $record)->execute()){
									return false;
					}
				}
			} else
			{
				unset($record['id']);
				if(!empty($record) && !App::get()
								->getDbConnection()
								->update($this->customFieldsTableName(), $record, ['id' => $this->customFieldsData['id']])->execute()) {
					return false;
				}
			}
			
			//After save might need this.
			$this->customFieldsData['id'] = $this->id;
		
			
			foreach(self::getCustomFieldModels() as $field) {
				if(!$field->getDataType()->afterSave(isset($this->customFieldsData[$field->databaseName]) ? $this->customFieldsData[$field->databaseName] : null, $this->customFieldsData)) {
					return false;
				}
			}
			
			return true;
		} catch(PDOException $e) {
			$uniqueKey = Utils::isUniqueKeyException($e);
			if ($uniqueKey) {				
				$this->setValidationError('customFields.' . $uniqueKey, ErrorCode::UNIQUE);				
				return false;
			} else {
				throw $e;
			}
		}
	}

	/**
	 * Get table name for custom fields data
	 * 
	 * @return string
	 */
	public static function customFieldsTableName() {
		
		if(is_a(static::class, Entity::class, true)) {
		
			$tables = static::getMapping()->getTables();		
			$mainTableName = array_keys($tables)[0];
		} else
		{
			//ActiveRecord
			$mainTableName = static::model()->tableName();
		}
		
		return $mainTableName.'_custom_fields';
	}
}
