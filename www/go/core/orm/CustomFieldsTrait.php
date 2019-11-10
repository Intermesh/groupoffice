<?php
namespace go\core\orm;

use go\core\App;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\validate\ErrorCode;
use go\core\model\Field;
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
	private $customFieldsIsNew;
	
	/**
	 * Get all custom fields data for an entity
	 * 
	 * @return array
	 */
	public function getCustomFields($asText = false) {
		$fn = $asText ? 'dbToText' : 'dbToApi';
		$record = $this->internalGetCustomFields();
		foreach(self::getCustomFieldModels() as $field) {
			if(empty($field->databaseName)) {
				continue; //For type Notes which doesn't store any data
			}
			$record[$field->databaseName] = $field->getDataType()->$fn(isset($record[$field->databaseName]) ? $record[$field->databaseName] : null, $record);			
		}
		return $record;	
	}

	private static $preparedCustomFieldStmt = [];

	protected function internalGetCustomFields() {
		if(!isset($this->customFieldsData)) {

			if(!isset(self::$preparedCustomFieldStmt[$this->customFieldsTableName()])) {
				self::$preparedCustomFieldStmt[$this->customFieldsTableName()] = (new Query())
							->select('*')
							->from($this->customFieldsTableName(), 'cf')
							->where('cf.id = :id')
							->createStatement();
			}

			$stmt = self::$preparedCustomFieldStmt[$this->customFieldsTableName()];
			$stmt->bindValue(':id', $this->id);

			$stmt->execute();

			$record = $stmt->fetch();
			
			$this->customFieldsIsNew = !$record;
							
			if($record) {			
				
				$columns = Table::getInstance(static::customFieldsTableName())->getColumns();		
				foreach($columns as $name => $column) {					
					$record[$name] = $column->castFromDb($record[$name]);					
				}			
				
				$this->customFieldsData = $record;
				
			} else
			{
				$this->customFieldsData = [];
			}
		}
		
		return $this->customFieldsData;//array_filter($this->customFieldsData, function($key) {return $key != 'id';}, ARRAY_FILTER_USE_KEY);
	}


	
	//for legacy modules
	public function setCustomFieldsJSON($json) {
		$data = json_decode($json, true);
		$this->setCustomFields($data);
	}
	
	/**
	 * Set custom field data
	 * 
	 * The data array may hold partial data. It will be merged into the existing
	 * data.
	 * 
	 * @param array $data
	 * @return $this
	 */
	public function setCustomFields(array $data, $asText = false) {			
		$this->customFieldsData = array_merge($this->internalGetCustomFields(), $this->normalizeCustomFieldsInput($data, $asText));		
		
		$this->customFieldsModified = true;

		return $this;
	}
	
	/**
	 * Set a custom field value
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setCustomField($name, $value, $asText = false) {
		return $this->setCustomFields([$name => $value], $asText);
	}
	
	private static $customFields;
	
	/**
	 * Check if custom fields are modified
	 * 
	 * @return bool
	 */
	protected function isCustomFieldsModified() {
		return $this->customFieldsModified;
	}
	
	/**
	 * Get all custom fields for this entity
	 * 
	 * @return Field
	 */
	public static function getCustomFieldModels() {
		if(!isset(self::$customFields)) {
			self::$customFields = Field::find()
						->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId')
						->where(['fs.entityId' => static::customFieldsEntityType()->getId()])->all();
		}
		
		return self::$customFields;
	}
	
	
	private function normalizeCustomFieldsInput($data, $asText = false) {
		$columns = Table::getInstance(static::customFieldsTableName())->getColumns();		
		foreach($columns as $name => $column) {
			if(array_key_exists($name, $data)) {
				$data[$name] = $column->normalizeInput($data[$name]);
			}
		}
			
		foreach(self::getCustomFieldModels() as $field) {	
			$fn = $asText ? 'textToDb' : 'apiToDb';
			//if client didn't post value then skip it
			if(array_key_exists($field->databaseName, $data)) {
				$data[$field->databaseName] = $field->getDataType()->$fn(isset($data[$field->databaseName]) ? $data[$field->databaseName] : null,  $data);			
			}
		}
		
		return $data;
	}

	protected function validateCustomFields() {
		if(!$this->customFieldsModified) {
			return true;
		}
		foreach(self::getCustomFieldModels() as $field) {
			if(!$field->getDataType()->validate(isset($this->customFieldsData[$field->databaseName]) ? $this->customFieldsData[$field->databaseName] : null, $field, $this)) {
				return false;
			}
		}
		return true;
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
			
			if($this->customFieldsIsNew) {
				if(!empty($record)) {								
					$record['id'] = $this->id;	
					if(!App::get()
									->getDbConnection()
									->insert($this->customFieldsTableName(), $record)->execute()){
									return false;
					}
					$this->customFieldsIsNew = false;
				}
			} else
			{
				unset($record['id']);
				if(!empty($record) && !App::get()
								->getDbConnection()
								->update($this->customFieldsTableName(), $record, ['id' => $this->id])->execute()) {
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

	private static $customFieldsTableName;

	/**
	 * Get table name for custom fields data
	 * 
	 * @return string
	 */
	public static function customFieldsTableName() {

		if(isset(self::$customFieldsTableName)) {
			return self::$customFieldsTableName;
		}
		$cls = static::customFieldsEntityType()->getClassName();
		
		if(is_a($cls, Entity::class, true)) {		
			$mainTableName = $cls::getMapping()->getPrimaryTable()->getName();				
		} else
		{
			//ActiveRecord
			$mainTableName = $cls::model()->tableName();
		}
		
		self::$customFieldsTableName = $mainTableName.'_custom_fields';

		return self::$customFieldsTableName;
	}

	/**
	 * The entity type the custom fields are for.
	 * 
	 * Usually this is the static::entityType() but sometimes a model extends another like with filesearch. Then you can override this function:
	 * 
	 * ```php
	 * use CustomFieldsTrait {
	 * 		customFieldsEntityType as origCustomFieldsEntityType;
	 * }
	 * 
	 * public static function customFieldsEntityType() {
	 * 		return File2::entityType();
	 * }
	 * ```
	 * 
	 * @return EntityType
	 */
	public static function customFieldsEntityType() {
		return static::entityType();
	}
	
	/**
	 * Defines filters for all custom fields
	 * 
	 * @param \go\core\orm\Filters $filters
	 */
	protected static function defineCustomFieldFilters(Filters $filters) {
		
		$fields = static::getCustomFieldModels();		
		
		foreach($fields as $field) {
			$field->getDataType()->defineFilter($filters);
		}		
	}
}
