<?php

namespace go\core\customfield;

use Exception;
use go\core\db\Criteria;
use go\core\orm\CustomFieldsModel;
use go\core\orm\Filters;
use go\core\orm\Query;

final class Attachments extends MultiSelect
{

	private $optionsToSave;
	public function onFieldSave(): bool
	{
		if ($this->field->isNew()) {
			$this->createMultiSelectTable();
		}

		return true;
	}

	public function getMultiSelectTableName(): string
	{
		return "core_customfields_attachments_" . $this->field->id;
	}

	/**
	 * @throws Exception
	 */
	public function createMultiSelectTable()
	{
		$tableName = $this->field->tableName();
		$multiSelectTableName = $this->getMultiSelectTableName();
		$entityColumn = $this->getTableDefinition()->getColumn('id');
		$type = $entityColumn->dataType . ($entityColumn->unsigned?' UNSIGNED':'');

		$sql = "CREATE TABLE IF NOT EXISTS `" . $multiSelectTableName . "` (
			`order` bigint unsigned DEFAULT 0,
			`modelId` $type NOT NULL,
			`blobId` BINARY(40) NOT NULL,
			`name` VARCHAR(192),
			`description` MEDIUMTEXT,
			PRIMARY KEY (`modelId`, `blobId`),
			KEY `idx_blobId` (`blobId`),
			CONSTRAINT `" . $multiSelectTableName . "_ibfk_1` FOREIGN KEY (`modelId`) REFERENCES `" . $tableName . "` (`id`) ON DELETE CASCADE,
		   CONSTRAINT `" . $multiSelectTableName . "_ibfk_2` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB;";

		return go()->getDbConnection()->query($sql);
	}

	public function beforeSave($value, CustomFieldsModel $model, $entity, &$record): bool
	{
		//remove options from record to be inserted and save them for the afterSave method.
		$this->optionsToSave = $value;
		unset($record[$this->field->databaseName]);
		return true;
	}

	public function afterSave($value,CustomFieldsModel &$customFieldModel, $entity) : bool
	{
		if(!isset($this->optionsToSave)) {
			return true;
		}
		$blobs = [];
		foreach($this->optionsToSave as $i => $attachment) {
			$blobs[$attachment['blobId']] = true;
			if(!go()->getDbConnection()->replace($this->getMultiSelectTableName(), [
				'modelId' => $entity->id,
				'order' => $i,
				'blobId' => $attachment['blobId'],
				'name' => $attachment['name'] ?? '',
				'description' => $attachment['description'] ?? ''
			])->execute()) {
				return false;
			}
		}


		$query = (new Query)->where(['modelId' => $entity->id]);
		if (!empty($blobs)) {
			$query->andWhere('blobId', 'not in', array_keys($blobs));
		}

		if(!go()->getDbConnection()->delete($this->getMultiSelectTableName(), $query)->execute()) {
			return false;
		}

		$this->optionsToSave = null;

		return true;
	}
	public function dbToApi($value, CustomFieldsModel $values, $entity) {

		//new model
		if($entity->isNew()) {
			return [];
		}

		return (new Query())
			->select("blobId, name, description")
			->from($this->getMultiSelectTableName())
			->where(['modelId' => $entity->id])
			->orderBy(['order'=>'ASC'])
			->all();
	}

	public function dbToText($value, CustomFieldsModel $values, $entity)
	{
		//new model
		if($entity->isNew()) {
			return "";
		}

		return implode(", ", (new Query())
			->selectSingleValue("blb.name")
			->join("core_blob", "blb", "blb.id = ms.blobId")
			->from($this->getMultiSelectTableName(), 'ms')
			->where(['modelId' => $entity->id])
			->orderBy(['blb.name' => 'ASC'])
			->all());
	}

	/**
	 * @throws \Exception
	 */
	public function textToDb($value, CustomFieldsModel $values, $entity)
	{
		if(empty($value)) {
			return [];
		}

		$texts = array_map('trim', explode(',', $value));

		$ids = (new Query())
			->selectSingleValue("id")
			->from("core_blob", 'b')
			->where(['name' => $texts])
			->andWhere(['fieldId' => $this->field->id])
			->orderBy(['b.name' => 'ASC'])
			->all();

		if(count($ids) != count($texts)) {
			throw new Exception("Invalid value(s) for multi select field '". $this->field->databaseName . "': ".implode(', ', $texts));
		}
		return $ids;
	}

	private static $joinCount = 0;

	private function getJoinAlias(): string
	{
		static::$joinCount++;

		return $this->field->databaseName .'_' . static::$joinCount;
	}

	/**
	 * Defines an entity filter for this field.
	 *
	 * @see Entity::defineFilters()
	 * @param Filters $filters
	 */
	public function defineFilter(Filters $filters)
	{
		$filters->addText($this->field->databaseName, function(Criteria $criteria, $comparator, $value, Query $query, array $filter)
		{
			$cls = $query->getModel();
			$primaryTableAlias = array_values($cls::getMapping()->getTables())[0]->getAlias();
			$joinAlias = $this->getJoinAlias();
			$query->join($this->getMultiSelectTableName(), $joinAlias, $joinAlias.'.modelId = '.$primaryTableAlias.'.id', 'left');

			if(isset($value[0]) && is_numeric($value[0])) {
				//When field option ID is passed by a saved filter
				$criteria->where($joinAlias. '.blobId', '=', $value);
			} else{
				//for text queries we must join the contacts.
				$alias = 'blob_' . uniqid();
				$query->join('core_blob', $alias, $alias . '.id = '.$joinAlias. '.blobId', 'left');
				$criteria->where($alias . '.name', $comparator, $value);
			}

		});
	}

}
