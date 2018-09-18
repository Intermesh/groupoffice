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
	
	public function getCustomFields() {
		if(!isset($this->customFieldsData)) {
			$record = (new Query())
							->select('*')
							->from($this->customFieldsTableName(), 'cf')
							->where(['id' => $this->id])->execute()->fetch();
							
			if($record) {			
				
				$columns = Table::getInstance(static::customFieldsTableName())->getColumns();		
				foreach($columns as $name => $column) {
					if(isset($record[$name])) {
						$record[$name] = $column->castFromDb($record[$name]);
					}
				}
				
				$this->customFieldsData = $record;
				
			} else
			{
				$this->customFieldsData = [];
			}
		}
		
		return $this->customFieldsData;
	}
	
	public function setCustomFields($data) {		
		$this->customFieldsData = array_merge($this->getCustomFields(), $this->normalizeCustomFieldsInput($data));
		$this->customFieldsModified = true;
	}
	
	
	private function normalizeCustomFieldsInput($data) {
		$columns = Table::getInstance(static::customFieldsTableName())->getColumns();		
		foreach($columns as $name => $column) {
			if(isset($data[$name])) {
				$data[$name] = $column->normalizeInput($data[$name]);
			}
		}
		
		$fields = Field::find()
						->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId')
						->where(['fs.entityId' => static::getType()->getId()]);
		
		foreach($fields as $field) {	

			$data[$field->databaseName] = $field->apiToDb(isset($data[$field->databaseName]) ? $data[$field->databaseName] : null, $data);			
		}
		return $data;
	}
	
	protected function saveCustomFields() {
		if(!$this->customFieldsModified) {
			return true;
		}
		
		try {
			if(!isset($this->customFieldsData['id'])) {
				$this->customFieldsData['id'] = $this->id;

				return App::get()
								->getDbConnection()
								->insert($this->customFieldsTableName(), $this->customFieldsData)->execute();
			} else
			{
				return App::get()
								->getDbConnection()
								->update($this->customFieldsTableName(), $this->customFieldsData, ['id' => $this->customFieldsData['id']])->execute();
			}
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
