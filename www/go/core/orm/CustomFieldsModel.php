<?php
namespace go\core\orm;

use GO\Base\Db\ActiveRecord;
use go\core\App;
use go\core\data\ArrayableInterface;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\http\Exception;
use go\core\Installer;
use go\core\model\Field;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

class CustomFieldsModel implements ArrayableInterface, \ArrayAccess, \JsonSerializable {

	private static $loopIds = [];

	private static $preparedCustomFieldStmt = [];

	/**
	 * @var Entity|ActiveRecord
	 */
	private $entity;

	/**
	 * Holds the custom fields record data
	 * @var array
	 */
	private $data;
	private $oldData;
	private $customFieldsIsNew;


	private $returnAsText = false;

	/**
	 * Set the default return type of @param bool $value
	 *
	 * @return CustomFieldsModel
	 * @see getCustomFields()
	 */
	public function returnAsText(bool $value = true)
	{
		$this->returnAsText = $value;

		return $this;
	}

	public function __construct($entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Set custom field values with key value arrat=y
	 * @param mixed $data
	 * @return $this
	 */
	public function setValues($data) {
		$old = $this->internalGetCustomFields();
		$this->data = array_merge($old, $this->normalizeCustomFieldsInput($data, $this->returnAsText));

		return $this;
	}

	/**
	 * Set custom field value
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setValue($name, $value) {
		return $this->setValues([$name => $value]);
	}

	/**
	 * Get custom field value
	 *
	 * @param string $name
	 * @return mixed
	 * @throws \Exception
	 */
	public function getValue($name) {

		$fn = $this->returnAsText ? 'dbToText' : 'dbToApi';
		$record = $this->internalGetCustomFields();

		$fields = self::getCustomFieldModels();

		if(!isset($fields[$name])) {
			throw new \Exception("Property '$name' doesn't exist");
		}

		$field = $fields[$name];

		//prevent infinite loop for function and template fields
		if(in_array($field->id, self::$loopIds)) {
			return "∞";
		}

		self::$loopIds[] = $field->id;

		$value =  $field->getDataType()->$fn(isset($record[$field->databaseName]) ? $record[$field->databaseName] : null, $this, $this->entity);

		//remove from loop check
		self::$loopIds = array_filter(self::$loopIds, function($id) use ($field) {
			return $id != $field->id;
		});

		return $value;
	}

	public function __get($name)
	{
		return $this->getValue($name);
	}

	public function __set($name, $value)
	{
		return $this->setValue($name, $value);
	}

	public function __isset($name)
	{
		try {
			$val = $this->getValue($name);
		} catch(\Exception $e) {
			return false;
		}
		return isset($val);
	}

	public function __unset($name)
	{
		$this->setValue($name, null);
	}

	public function isModified() {
		return $this->oldData != $this->data;
	}

	public function customFieldsTableName() {
		$cls = get_class($this->entity);

		return $cls::customFieldsTableName();
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	private function internalGetCustomFields() {
		if(!isset($this->data)) {

			if(!isset(self::$preparedCustomFieldStmt[$this->customFieldsTableName()])) {
				$query = (new Query())
					->select('*')
					->from($this->customFieldsTableName(), 'cf')
					->where('cf.id = :id');

				self::$preparedCustomFieldStmt[$this->customFieldsTableName()] = $query->createStatement();
			}

			$stmt = self::$preparedCustomFieldStmt[$this->customFieldsTableName()];
			$stmt->bindValue(':id', $this->entity->id);

			$stmt->execute();

			$record = $stmt->fetch();

			$stmt->closeCursor();

			$this->customFieldsIsNew = !$record;

			if($record) {

				$columns = Table::getInstance(static::customFieldsTableName())->getColumns();
				foreach($columns as $name => $column) {
					$record[$name] = $column->castFromDb($record[$name]);
				}

				$this->data = $record;

			} else
			{
				$record = [];
				$columns = Table::getInstance(static::customFieldsTableName())->getColumns();
				foreach($columns as $name => $column) {
					if($name == "id") {
						continue;
					}
					$record[$name] = $column->default;
				}

				$this->data = $record;
			}
		}

		return $this->data;//array_filter($this->customFieldsData, function($key) {return $key != 'id';}, ARRAY_FILTER_USE_KEY);
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

		if($data instanceof CustomFieldsModel)
		{
			$data = $data->toArray();
		}

		$columns = Table::getInstance(static::customFieldsTableName())->getColumns();
		foreach($columns as $name => $column) {
			if(array_key_exists($name, $data)) {
				if(empty($data[$name]) && $column->nullAllowed ) {
					if(!is_numeric($data[$name]) || (int) $data[$name] !== 0) {
						$data[$name] = null;
					}
				} else {
					$data[$name] = $column->normalizeInput($data[$name]);
				}
			}
		}
		$fn = $asText ? 'textToDb' : 'apiToDb';
		foreach($this->getCustomFieldModels() as $field) {
			//if client didn't post value then skip it
 			if(array_key_exists($field->databaseName, $data)) {
				$data[$field->databaseName] = $field->getDataType()->$fn(isset($data[$field->databaseName]) ? $data[$field->databaseName] : null,  $this, $this->entity);
			}
		}

		return $data;
	}

	/**
	 * @return Field[]
	 */
	public function getCustomFieldModels() {
		$cls = get_class($this->entity);

		$models = $cls::getCustomFieldModels();
		return $models;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function validate() {
		if(!$this->isModified()) {
			return true;
		}
		foreach($this->getCustomFieldModels() as $field) {
			if(!$field->getDataType()->validate(isset($this->data[$field->databaseName]) ? $this->data[$field->databaseName] : null, $field, $this->entity)) {
				return false;
			}
		}
		return true;
	}

	public function getAsText() {
		$this->internalGetCustomFields();
		$text = clone $this;
		$text->returnAsText(true);
		return $text;
	}

	public function toArray($properties = null) {
		$fn = $this->returnAsText ? 'dbToText' : 'dbToApi';
		$record = $this->internalGetCustomFields();
		foreach($this->getCustomFieldModels() as $field) {
			if(empty($field->databaseName)) {
				continue; //For type Notes which doesn't store any data
			}
			$record[$field->databaseName] = $field->getDataType()->$fn(isset($record[$field->databaseName]) ? $record[$field->databaseName] : null, $this, $this->entity);
		}
		unset($record['id']);
		return $record;
	}

	public function save() {
		try {

			if(Installer::isInstalling()) {
				return true;
			}

			$record = $this->data;

			foreach($this->getCustomFieldModels() as $field) {
				if(!$field->getDataType()->beforeSave(isset($this->data[$field->databaseName]) ? $this->data[$field->databaseName] : null, $this, $this->entity,$record)) {
					return false;
				}
			}

			//beforeSave might change $record
			if(!$this->isModified() && $record == $this->data) {
				return true;
			}

			//Set modifiedAt because otherwise the entity might have no change at all. Then no change will be logged for
			//JMAP sync
			if(property_exists($this->entity, 'modifiedAt') && !$this->entity->isModified(['modifiedAt'])) {
				$this->entity->modifiedAt = new DateTime();
			}

			if($this->customFieldsIsNew) {

				//if(!empty($record)) { //always create record for select fields with foreign keys!
				$record['id'] = $this->entity->id;
				if(!App::get()
					->getDbConnection()
					->insert($this->customFieldsTableName(), $record)->execute()){
					return false;
				}
				$this->customFieldsIsNew = false;
				//}
			} else {
				if(!empty($record) && !App::get()
						->getDbConnection()
						->update($this->customFieldsTableName(), $record, ['id' => $this->entity->id])->execute()) {
					return false;
				}
			}

			$this->data = $record;

			//After save might need this.
			//$this->data['id'] = $this->entity->id;

			foreach($this->getCustomFieldModels() as $field) {
				if(!$field->getDataType()->afterSave(isset($this->data[$field->databaseName]) ? $this->data[$field->databaseName] : null, $this, $this->entity)) {
					return false;
				}
			}

			return true;
		} catch(PDOException $e) {
			$uniqueKey = Utils::isUniqueKeyException($e);
			if ($uniqueKey) {
				$this->entity->setValidationError('customFields.' . $uniqueKey, ErrorCode::UNIQUE);
				return false;
			} else {
//				throw $e;
				throw new \Exception($e->getMessage());
			}
		}
	}

	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	public function offsetSet($offset, $value)
	{
		return $this->__set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}
}