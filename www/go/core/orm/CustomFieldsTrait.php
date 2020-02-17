<?php
namespace go\core\orm;

use Exception;
use go\core\App;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\validate\ErrorCode;
use go\core\model\Field;
use PDOException;
use go\core\util\JSON;

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
   * @param bool $asText Returns all values printable as text. Useful for templates and exports.
   * @return array
   * @throws Exception
   */
	public function getCustomFields($asText = false) {
		$fn = $asText ? 'dbToText' : 'dbToApi';
		$record = $this->internalGetCustomFields();
		foreach(self::getCustomFieldModels() as $field) {
			if(empty($field->databaseName)) {
				continue; //For type Notes which doesn't store any data
			}
			$record[$field->databaseName] = $field->getDataType()->$fn(isset($record[$field->databaseName]) ? $record[$field->databaseName] : null, $record, $this);
		}
		return $record;	
	}

	private static $preparedCustomFieldStmt = [];

  /**
   * @return array
   * @throws Exception
   */
	protected function internalGetCustomFields() {
		if(!isset($this->customFieldsData)) {

			if(!isset(self::$preparedCustomFieldStmt[$this->customFieldsTableName()])) {
				$query = (new Query())
							->select('*')
							->from($this->customFieldsTableName(), 'cf')
							->where('cf.id = :id');

				self::$preparedCustomFieldStmt[$this->customFieldsTableName()] = $query->createStatement();
			}

			$stmt = self::$preparedCustomFieldStmt[$this->customFieldsTableName()];
			$stmt->bindValue(':id', $this->id);

			$stmt->execute();

			$record = $stmt->fetch();

			$stmt->closeCursor();
			
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
  /**
   * Setter for legacy modules
   *
   * @param $json
   * @throws Exception
   */
	public function setCustomFieldsJSON($json) {
		$data = JSON::decode($json, true);
		$this->setCustomFields($data);
	}

	/**
	 * Get the old custom fields data. Returns null if they were never modified.
	 *
	 * @return null|[]
	 */
	public function oldCustomFields() {
		return $this->oldCustomFieldsData;
	}

	/**
	 * Get modified custom fields with new and old value
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getModifiedCustomFields() {
		if(!$this->isCustomFieldsModified()) {
			return [];
		}
		$oldCf = $this->oldCustomFields();
		$newCf = $this->getCustomFields();

		$mod = [];
		foreach($newCf as $key => $value) {
			if(!array_key_exists($key, $oldCf)) {
				$mod[$key] = [$value, null];
			} elseif($value !== $oldCf[$key]) {
				$mod[$key] = [$value, $oldCf[$key]];
			}
		}

		return $mod;

	}

	private $oldCustomFieldsData;

  /**
   * Set custom field data
   *
   * The data array may hold partial data. It will be merged into the existing
   * data.
   *
   * @param array $data
   * @return $this
   * @throws Exception
   */
	public function setCustomFields(array $data, $asText = false) {
		if(!isset($this->oldCustomFieldsData)) {
			$this->oldCustomFieldsData = $this->internalGetCustomFields();
		}
	/**
	 * Set custom field data
	 *
	 * The data array may hold partial data. It will be merged into the existing
	 * data.
	 *
	 * @param array $data
	 * @param bool $asText
	 * @return $this
	 * @throws Exception
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
   * @throws Exception
   */
	public function setCustomField($name, $value, $asText = false) {
		return $this->setCustomFields([$name => $value], $asText);
	}
	
	private static $customFieldModels;
	
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
   * @return Field[]
   * @throws Exception
   */
	public static function getCustomFieldModels() {
		$cacheKey = 'custom-field-models-' . static::customFieldsEntityType()->getId();
	 	$m = go()->getCache()->get($cacheKey);
		if(!$m) {
			$m = Field::find(['id', 'databaseName', 'fieldSetId', 'type', 'options', 'required'], true)
						->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId')
						->where(['fs.entityId' => static::customFieldsEntityType()->getId()])->all();

			go()->getCache()->set($cacheKey, $m);
		}
		
		return $m;
	}

  /**
   * Converts user input to database formats.
   *
   * @param $data
   * @param bool $asText
   * @return mixed
   * @throws Exception
   */
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
				$data[$field->databaseName] = $field->getDataType()->$fn(isset($data[$field->databaseName]) ? $data[$field->databaseName] : null,  $data, $this);
			}
		}
		
		return $data;
	}

  /**
   * @return bool
   * @throws Exception
   */
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
   * @throws Exception
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
   * @throws Exception
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
   * @param Filters $filters
   * @throws Exception
   */
	protected static function defineCustomFieldFilters(Filters $filters) {
		
		$fields = static::getCustomFieldModels();		
		
		foreach($fields as $field) {
			if(!$filters->hasFilter($field->databaseName)) {
				$field->getDataType()->defineFilter($filters);
			}
		}		
	}
}
