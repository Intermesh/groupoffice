<?php
namespace go\core\model;

use GO;
use go\core\acl\model\AclItemEntity;
use go\core\db\Table;
use go\core\orm\EntityType;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\core\model\FieldSet;
use go\core\customfield\Base;

/**
 * Field
 * 
 * A custom field
 */
class Field extends AclItemEntity {

	/**
	 * The Entity ID
	 * 
	 * @var int
	 */
	public $id;
	
	/**
	 * Display name
	 * @var string 
	 */
	public $name;
	
	/**
	 * Foreign key for fieldSet
	 * @var int
	 */
	public $fieldSetId;
	
	/**
	 * Sort order
	 * 
	 * @var int
	 */
	public $sortOrder;
	protected $options;
	
	
	/**
	 * The database column name
	 * 
	 * @var string 
	 */
	public $databaseName;
	
	/**
	 * True if an entry is requied
	 * @var boolean
	 */
	public $required;
	
	/**
	 * Hint text to display in the form
	 * @var string
	 */
	public $hint;
	
	/**
	 * Field prefix
	 * 
	 * eg. :"€:
	 * 
	 * @var string
	 */
	public $prefix;
	
	/**
	 * Field suffix
	 * 
	 * eg. "%"
	 * 
	 * @var string 
	 */
	public $suffix;
	
	/**
	 * Data type
	 * 
	 * @var string
	 */
	public $type;
	
	/**
	 * Modified at time
	 * 
	 * @var DateTime
	 */
	public $modifiedAt;
	
	/**
	 * Created at time
	 * ]
	 * @var DateTime
	 */
	public $createdAt;
	
	
	private $default;
	private $defaultModified = false;
	private $unique;
	private $uniqueModified = false;
	private $dataType;
	
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_customfields_field', 'f');
	}

	protected static function aclEntityClass() {
		return FieldSet::class;
	}

	protected static function aclEntityKeys() {
		return ['fieldSetId' => 'id'];
	}
	
	protected function internalValidate() {
		
		$this->getDataType()->onFieldValidate();
		
		return parent::internalValidate();
	}

//	/**
//	 * LEGACY. $field->multiselect is used many times.
//	 * fix before removing a property
//	 */
//	public function getMultiselect() {
//		return $this->getOptions('multiselect');
//	}

	/**
	 * Get field options. 
	 * 
	 * These options can vary per data type.
	 * 
	 * eg. "multiselect" for select fields or maxLength for text fields.
	 * 
	 * @return array
	 */
	public function getOptions() {
		return empty($this->options) ? [] : json_decode($this->options, true);
	}

	public function setOptions($options) {
		$this->options = json_encode(array_merge($this->getOptions(), $options));
	}

	/**
	 * Get field option
	 * 
	 * @see getOptions()
	 * @param string $name
	 * @return mixed
	 */
	public function getOption($name) {
		$o = $this->getOptions();
		return isset($o[$name]) ? $o[$name] : null;
	}

	/**
	 * Set a field option
	 * 
	 * @see getOptions()
	 * @param string $name
	 * @param mixed $value
	 */
	public function setOption($name, $value) {
		$o = $this->getOptions();
		$o[$name] = $value;
		$this->setOptions($o);
	}
	
	/**
	 * Get default value for the column
	 * 
	 * @return mixed
	 */	
	public function getDefault() {
		if($this->defaultModified || $this->isNew()) {
			return $this->default;
		}
		
		$c = Table::getInstance($this->tableName())->getColumn($this->databaseName);
		
		if(!$c) {
			go()->debug("Column for custom field ".$this->databaseName." not found in ". $this->tableName());
			return null;
		}
		
		return $c->default;
	}
	
	public function setDefault($v) {
		$this->default = $v;
		$this->defaultModified = true;
	}
	
	
	public function getUnique() {
		if($this->uniqueModified || $this->isNew()) {
			return $this->unique;
		}
		
		$c = Table::getInstance($this->tableName())->getColumn($this->databaseName);
		
		if(!$c) {
			go()->debug("Column for custom field ".$this->databaseName." not found in ". $this->tableName());
			return null;
		}
		
		return !!$c->unique;
						
	}
	
	public function setUnique($v) {
		$this->unique = $v;
		$this->uniqueModified = true;
	}

	/**
	 * The data type object
	 * 
	 * @return Base
	 */
	public function getDataType() {
		
		if(!isset($this->dataType)) {			
			$dataType = Base::findByName($this->type);
			$this->dataType = (new $dataType($this));
		}		
		return $this->dataType;
	}
	
	public function setDataType($values) {
		$this->getDataType()->setValues($values);
	}

	protected function internalSave() {
		if(!parent::internalSave()) {
			return false;
		}
		
		try {
			go()->getDbConnection()->pauseTransactions();
			$this->getDataType()->onFieldSave();
		} catch(\Exception $e) {
			go()->warn($e);
			go()->getDbConnection()->resumeTransactions();
			if($this->isNew()) {
				static::delete($this->primaryKeyValues());				
			}

			$this->setValidationError('id', \go\core\validate\ErrorCode::GENERAL, $e->getMessage());
			
			return false;
		} 
		go()->getDbConnection()->resumeTransactions();
		
		
		return true;
	}

	protected static function internalDelete(Query $query) {
		try {
			go()->getDbConnection()->pauseTransactions();
			$fields = Field::find()->mergeWith($query);
			foreach($fields as $field) {
				$field->getDataType()->onFieldDelete();
			}
		} catch(\Exception $e) {
			go()->warn($e);
			return false;
		} finally {
			go()->getDbConnection()->resumeTransactions();
		}
		
		return parent::internalDelete($query);
}

	/**
	 * Get the table name this field is stored in.
	 * 
	 * @return sting
	 */
	public function tableName() {
		$fieldSet = FieldSet::findById($this->fieldSetId);
		$entityType = EntityType::findByName($fieldSet->getEntity());
		if(!$entityType) {
			throw new \Exception("EntityType not found for custom field ".$this->name.' ('. $this->id.')');
		}
		$entityCls = $entityType->getClassName();
		return $entityCls::customFieldsTableName(); //From customfieldstrait
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add('fieldSetId', function (\go\core\db\Criteria $criteria, $value){
							$criteria->andWhere(['fieldSetId' => $value]);
						});
	}
	
	/**
	 * Find all fields for an entity
	 * 
	 * @param int|string $entityTypeId
	 * @return Query
	 */
	public static function findByEntity($entityTypeId) {
		if(!is_numeric($entityTypeId)) {
			$entityTypeId = EntityType::findByName($entityTypeId)->getId();
		}
		return static::find()->where(['fs.entityId' => $entityTypeId])->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId');
	}


	/**
	 * Find or create custom field
	 * 
	 * @param string $entity eg. "User"
	 * @param string $fieldSetName eg. "Forum"
	 * @param string $databaseName eg. "numberOfPosts"
	 * @param string $name eg "Number of posts"
	 * @param string $type Type of custom field eg. Type
	 * @param array $values extra values to set on the field.
	 * 
	 * @return static
	 */
	public static function create($entity, $fieldSetName, $databaseName, $name, $type = 'Text', $values = []) {
		$field = Field::findByEntity($entity)->where(['databaseName' => $databaseName])->single();

		if($field) {
			return $field;
		}

		$fieldSet = FieldSet::findByEntity($entity)->where(['name' => $fieldSetName])->single();
		if(!$fieldSet) {
			$fieldSet = new FieldSet();		
			$fieldSet->name = $fieldSetName;
			$fieldSet->setEntity($entity);
			if(!$fieldSet->save()) {
				throw new \Exception("Could not save fieldset");
			}
		}

		$field = new Field();
		$field->databaseName = $databaseName;
		$field->name = $name;
		$field->type = $type;
		$field->fieldSetId = $fieldSet->id;
		$field->setValues($values);
		if(!$field->save()) {
			throw new \Exception("Could not save field");
		}
		return $field;
	}

}
