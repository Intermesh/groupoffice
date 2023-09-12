<?php
namespace go\core\orm;

use ArrayAccess;
use GO\Base\Db\ActiveRecord;
use go\core\App;
use go\core\data\ArrayableInterface;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use Exception;
use go\core\Installer;
use go\core\model\Field;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use JsonSerializable;
use LogicException;
use PDOException;

class CustomFieldsModel implements ArrayableInterface, ArrayAccess, JsonSerializable {

	private static $loopIds = [];


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
	public function returnAsText(bool $value = true): CustomFieldsModel
	{
		$this->returnAsText = $value;

		return $this;
	}

	public function __construct($entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Set custom field values with key value array
	 * @param array|CustomFieldsModel $data
	 * @return $this
	 * @throws Exception
	 */
	public function setValues($data): CustomFieldsModel
	{
		$old = $this->internalGetCustomFields();
		$new = $this->normalizeCustomFieldsInput($data, $this->returnAsText);
		$this->data = array_merge($old, $new);

		return $this;
	}

	/**
	 * Set custom field value
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 * @throws Exception
	 */
	public function setValue(string $name, $value): CustomFieldsModel
	{
		return $this->setValues([$name => $value]);
	}

	/**
	 * Get custom field value
	 *
	 * @param string $name
	 * @return mixed
	 * @throws Exception
	 */
	public function getValue(string $name) {

		$fn = $this->returnAsText ? 'dbToText' : 'dbToApi';
		$record = $this->internalGetCustomFields();

		$fields = self::getCustomFieldModels();

		$field = $fields[$name];

		//prevent infinite loop for function and template fields
		if(in_array($field->id, self::$loopIds)) {
			return "∞";
		}

		self::$loopIds[] = $field->id;

		$value =  $field->getDataType()->$fn($record[$field->databaseName] ?? null, $this, $this->entity);

		//remove from loop check
		self::$loopIds = array_filter(self::$loopIds, function($id) use ($field) {
			return $id != $field->id;
		});

		return $value;
	}



	/**
	 * @throws Exception
	 */
	public function __get($name)
	{
		return $this->getValue($name);
	}

	/**
	 * @throws Exception
	 */
	public function __set($name, $value)
	{
		return $this->setValue($name, $value);
	}

	public function __isset($name)
	{
		try {
			$val = $this->getValue($name);
		} catch(Exception $e) {
			return false;
		}
		return isset($val);
	}

	/**
	 * @throws Exception
	 */
	public function __unset($name)
	{
		$this->setValue($name, null);
	}

	public function isModified(): bool
	{
		return $this->oldData != $this->data;
	}



	private function convertValue($name, $value) {
		$fn = $this->returnAsText ? 'dbToText' : 'dbToApi';
		$fields = self::getCustomFieldModels();
		if(!isset($fields[$name])) {
			throw new LogicException("Property '$name' doesn't exist");
		}
		$field = $fields[$name];
		return $field->getDataType()->$fn($value, $this, $this->entity);
	}


	/**
	 * Get modified custom fields with new and old value
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getModified(): array
	{
		if(!$this->isModified()) {
			return [];
		}
		$oldCf = $this->oldData;
		$newCf = $this->internalGetCustomFields();

		$mod = [];
		foreach($newCf as $key => $value) {
			if($key == 'id') {
				continue;
			}
			if (!is_array($oldCf) || !array_key_exists($key, $oldCf)) {
				$mod[$key] = [$this->convertValue($key, $value), null];
			} elseif($value !== $oldCf[$key]) {
				$mod[$key] = [$this->convertValue($key, $value), $this->convertValue($key, $oldCf[$key])];
			}
		}

		return $mod;

	}

	/**
	 * @throws Exception
	 */
	public function customFieldsTableName(): string
	{
		$cls = get_class($this->entity);
		/** @var CustomFieldsTrait $cls */

		return $cls::customFieldsTableName();
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	private function internalGetCustomFields(): array
	{
		if(!isset($this->data)) {

			$stmt = go()->getDbConnection()->getCachedStatment('cf-' . $this->customFieldsTableName());
			if(!$stmt) {
				$query = (new Query())
					->select('*')
					->from($this->customFieldsTableName(), 'cf')
					->where('cf.id = :id')
					->bind(':id', $this->entity->id());

				$stmt = $query->createStatement();
				go()->getDbConnection()->cacheStatement('cf-' . $this->customFieldsTableName(), $stmt);
			} else {
				$stmt->bindValue(':id', $this->entity->id());
			}

			$stmt->execute();

			$record = $stmt->fetch();

			$stmt->closeCursor();

			$this->customFieldsIsNew = !$record;

			$columns = Table::getInstance(static::customFieldsTableName())->getColumns();
			if($record) {
				foreach($columns as $name => $column) {
					$record[$name] = $column->castFromDb($record[$name]);
				}

			} else
			{
				$record = [];
				foreach($columns as $name => $column) {
					if($name == "id") {
						continue;
					}
					$record[$name] = $column->default;
				}

			}
			$this->data = $this->oldData = $record;
		}

		return $this->data;//array_filter($this->customFieldsData, function($key) {return $key != 'id';}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Converts user input to database formats.
	 *
	 * @param array|CustomFieldsModel $data
	 * @param bool $asText
	 * @return array
	 * @throws Exception
	 */
	private function normalizeCustomFieldsInput($data, bool $asText = false) : array {

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
	 * @throws Exception
	 */
	public function getCustomFieldModels(): array
	{
		$cls = get_class($this->entity);
		/** @var CustomFieldsTrait $cls */

		return $cls::getCustomFieldModels();
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function validate(): bool
	{
		if(!$this->isModified()) {
			return true;
		}
		foreach($this->getCustomFieldModels() as $field) {
			if(!$field->getDataType()->validate($this->data[$field->databaseName] ?? null, $field, $this->entity)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @throws Exception
	 */
	public function getAsText(): CustomFieldsModel
	{
		$this->internalGetCustomFields();
		$text = clone $this;
		$text->returnAsText(true);
		return $text;
	}

	/**
	 * @throws Exception
	 */
	public function toArray(array $properties = null): array
	{
		$fn = $this->returnAsText ? 'dbToText' : 'dbToApi';
		$record = $this->internalGetCustomFields();
		foreach($this->getCustomFieldModels() as $field) {
			if(empty($field->databaseName)) {
				continue; //For type Notes which doesn't store any data
			}
			$record[$field->databaseName] = $field->getDataType()->$fn($record[$field->databaseName] ?? null, $this, $this->entity);
		}
		unset($record['id']);
		return $record;
	}

	/**
	 * @throws Exception
	 */
	public function save(): bool
	{
		try {

			if(Installer::isInstalling()) {
				return true;
			}

			$record = $this->internalGetCustomFields();

			foreach($this->getCustomFieldModels() as $field) {
				if(!$field->getDataType()->beforeSave($this->data[$field->databaseName] ?? null, $this, $this->entity,$record)) {
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
				$record['id'] = $this->entity->id();
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
						->update($this->customFieldsTableName(), $record, ['id' => $this->entity->id()])->execute()) {
					return false;
				}
			}

			$this->data = $record;

			//After save might need this.
			//$this->data['id'] = $this->entity->id;

			foreach($this->getCustomFieldModels() as $field) {
				if(!$field->getDataType()->afterSave($this->data[$field->databaseName] ?? null, $this, $this->entity)) {
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
				throw new Exception($e->getMessage());
			}
		}
	}

	public function offsetExists($offset): bool
	{
		return $this->__isset($offset);
	}
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	public function offsetSet($offset, $value) : void
	{
		$this->__set($offset, $value);
	}

	public function offsetUnset($offset) : void
	{
		$this->__unset($offset);
	}

	/**
	 * @throws Exception
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}