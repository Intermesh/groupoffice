<?php

namespace go\modules\core\customfields\model;

use Exception;
use GO;
use go\core\acl\model\AclItemEntity;
use go\core\orm\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\ErrorHandler;
use go\core\orm\EntityType;
use go\modules\core\customfields\type\Base;
use go\modules\core\customfields\model\FieldSet;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class Field extends AclItemEntity {

	/**
	 * The Entity ID
	 * 
	 * @var int
	 */
	public $id;
	public $name;
	public $fieldSetId;
	public $sortOrder;
	protected $options;
	public $databaseName;
	public $required;
	public $hint;
	public $prefix;
	public $suffix;
	public $type;
	public $modifiedAt;
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

	/**
	 * LEGACY. $field->multiselect is used many times.
	 * fix before removing a property
	 */
	public function getMultiselect() {
		return $this->getOptions('multiselect');
	}

	public function getOptions() {
		return empty($this->options) ? [] : json_decode($this->options, true);
	}

	public function setOptions($options) {
		$this->options = json_encode(array_merge($this->getOptions(), $options));
	}

	public function getOption($name) {
		$o = $this->getOptions();
		return isset($o[$name]) ? $o[$name] : null;
	}

	public function setOption($name, $value) {
		$o = $this->getOptions();
		$o[$name] = $value;
		$this->setOptions($o);
	}
	
	
	public function getDefault() {
		if($this->defaultModified || $this->isNew()) {
			return $this->default;
		}
		
		$c = Table::getInstance($this->tableName())->getColumn($this->databaseName);
		
		if(!$c) {
			GO()->debug("Column for custom field ".$this->databaseName." not found in ". $this->tableName());
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
			GO()->debug("Column for custom field ".$this->databaseName." not found in ". $this->tableName());
			return null;
		}
		
		return !!$c->unique;
						
	}
	
	public function setUnique($v) {
		$this->unique = $v;
		$this->uniqueModified = true;
	}

	/**
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
		return $this->getDataType()->onFieldSave();
	}

	protected function internalDelete() {
		if(!parent::internalDelete()) {
			return false;
		}
		return $this->getDataType()->onFieldDelete();
	}

	public function tableName() {
		$fieldSet = FieldSet::findById($this->fieldSetId);
		$entityType = EntityType::findByName($fieldSet->getEntity());
		$entityCls = $entityType->getClassName();
		return $entityCls::customFieldsTableName(); //From customfieldstrait
	}

	public static function filter(Query $query, array $filter) {

		if (!empty($filter['fieldSetId'])) {
			$query->andWhere(['fieldSetId' => $filter['fieldSetId']]);
		}

		return parent::filter($query, $filter);
	}
	
	/**
	 * Find all fields for an entity
	 * 
	 * @param int $entityTypeId
	 * @return Query
	 */
	public static function findByEntity($entityTypeId) {
		return static::find()->where(['fs.entityId' => $entityTypeId])->join('core_customfields_field_set', 'fs', 'fs.id = f.fieldSetId');
	}

}
