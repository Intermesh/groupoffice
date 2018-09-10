<?php
namespace go\modules\core\customfields\model;

use go\core\acl\model\AclItemEntity;
use go\modules\core\customfields\model\FieldSet;



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
	
	public $helptext;
	
	public $prefix;
	
	public $suffix;
	
	public $datatype;

	
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
		$this->options = json_encode($options);
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
	
	private function getDataType() {
		$dataType = \go\modules\core\customfields\datatype\Base::findByName($this->datatype);
		return (new $dataType($this));
	}
	
	public function apiToDb($value, $values) {
					
		return $this->getDataType()->apiToDb($value, $values);
	}
	
	public function dbToApi($value, $values) {		
		return $this->getDataType()->dbToApi($value, $values);
	}
	
	public static function filter(\go\core\db\Query $query, array $filter) {
		
		if(!empty($filter['fieldSetId'])) {
			$query->andWhere(['fieldSetId' => $filter['fieldSetId']]);
		}
		
		return parent::filter($query, $filter);
	}
}
