<?php

namespace go\modules\core\customfields\model;

use Exception;
use GO;
use go\core\acl\model\AclItemEntity;
use go\core\db\Query;
use go\core\orm\EntityType;
use go\modules\core\customfields\datatype\Base;
use go\modules\core\customfields\model\FieldSet;
use stdClass;
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
	public $helptext;
	public $prefix;
	public $suffix;
	public $type;
	public $modifiedAt;
	public $createdAt;
	public $default = "";

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

	/**
	 * 
	 * @return Base
	 */
	private function getDataType() {
		$dataType = Base::findByName($this->type);
		return (new $dataType($this));
	}

	protected function internalSave() {

		$this->alterColumn();

		return parent::internalSave();
	}

	protected function internalDelete() {

		$this->dropColumn();

		return parent::internalDelete();
	}

	private function getTableName() {
		$fieldSet = FieldSet::findById($this->fieldSetId);
		$entityType = EntityType::findByName($fieldSet->getEntity());
		$entityCls = $entityType->getClassName();
		return $entityCls::customFieldsTableName(); //From customfieldstrait
	}

	private function alterColumn() {
		
		$table = $this->getTableName();
		$fieldSql = $this->getDataType()->getFieldSQL();
		if ($this->isNew()) {
			$sql = "ALTER TABLE `" . $table . "` ADD `" . $this->databaseName . "` " . $fieldSql . ";";
		} else {
			$oldName = $this->isModified('databaseName') ? $this->getOldValue("databaseName") : $this->databaseName;
			$sql = "ALTER TABLE `" . $table . "` CHANGE `" . $oldName . "` `" . $this->databaseName . "` " . $fieldSql;
		}
		
		try {
			GO()->getDbConnection()->query($sql);
		} catch (Exception $e) {
			\go\core\ErrorHandler::logException($e);
		}
	}

	private function dropColumn() {
		$sql = "ALTER TABLE `" . $this->getTableName() . "` DROP `" . $this->databaseName . "`";

		try {
			GO()->getDbConnection()->query($sql);
		} catch (Exception $e) {
			\go\core\ErrorHandler::logException($e);
		}
	}

	public function apiToDb($value, $values) {

		return $this->getDataType()->apiToDb($value, $values);
	}

	public function dbToApi($value, $values) {
		return $this->getDataType()->dbToApi($value, $values);
	}

	public static function filter(Query $query, array $filter) {

		if (!empty($filter['fieldSetId'])) {
			$query->andWhere(['fieldSetId' => $filter['fieldSetId']]);
		}

		return parent::filter($query, $filter);
	}

}
