<?php
namespace go\core\orm;

use go\core\App;
use go\core\db\Query;

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
				$this->customFieldsData = $record;
			} else
			{
				$this->customFieldsData = [];
			}
		}
		
		return $this->customFieldsData;
	}
	
	public function setCustomFields($data) {		
		$this->customFieldsData = $this->normalizeCustomFieldsInput($data);
		$this->customFieldsModified = true;
	}
	
	
	private function normalizeCustomFieldsInput($data) {
		$columns = \go\core\db\Table::getInstance(static::customFieldsTableName())->getColumns();		
		foreach($columns as $name => $column) {
			if(isset($data[$name])) {
				$data[$name] = $column->normalizeInput($data[$name]);
			}
		}
		
		$fields = \go\modules\core\customfields\model\Field::find()
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
		
		$this->customFieldsData['id'] = $this->id;
		
		return App::get()
						->getDbConnection()
						->replace($this->customFieldsTableName(), $this->customFieldsData)->execute();
	}


	public static function customFieldsTableName() {
		$tables = static::getMapping()->getTables();		
		$mainTableName = array_keys($tables)[0];
		
		return $mainTableName.'_custom_fields';
	}
}
