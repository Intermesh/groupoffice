<?php

namespace go\modules\community\addressbook\customfield;

use Exception;
use go\core\customfield\MultiSelect;
use go\core\db\Criteria;
use go\core\orm\CustomFieldsModel;
use go\core\orm\Filters;
use go\core\orm\Query;

final class MultiContact extends MultiSelect
{
	public function onFieldSave(): bool
	{
		if ($this->field->isNew()) {
			$this->createMultiSelectTable();
		}

		return true;
	}

	public function getMultiSelectTableName(): string
	{
		return "addressbook_customfields_multicontact_" . $this->field->id;
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
			`id` $type NOT NULL,
			`contactId` int(11) NOT NULL,
			PRIMARY KEY (`id`,`contactId`),
			KEY `contactId` (`contactId`)
		) ENGINE=InnoDB;";

		if(!go()->getDbConnection()->query($sql)) {
			return false;
		}

		$sql = "ALTER TABLE `" . $multiSelectTableName . "`
			ADD CONSTRAINT `" . $multiSelectTableName . "_ibfk_1` FOREIGN KEY (`id`) REFERENCES `" . $tableName . "` (`id`) ON DELETE CASCADE,
		  ADD CONSTRAINT `" . $multiSelectTableName . "_ibfk_2` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;";

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

		foreach($this->optionsToSave as $optionId) {
			if(!go()->getDbConnection()->replace($this->getMultiSelectTableName(), ['id' => $entity->id, 'contactId' => $optionId])->execute()) {
				return false;
			}
		}


		$query  = (new Query)->where(['id' => $entity->id]);
		if (!empty($this->optionsToSave)) {
			$query	->andWhere('contactId', 'not in', $this->optionsToSave);
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
			->selectSingleValue("contactId")
			->from($this->getMultiSelectTableName())
			->where(['id' => $entity->id])
			->all();
	}

	public function dbToText($value, CustomFieldsModel $values, $entity)
	{
		//new model
		if($entity->isNew()) {
			return "";
		}

		return implode(", ", (new Query())
			->selectSingleValue("ac.name")
			->join("addressbook_contact", "ac", "ac.id = ms.contactId")
			->from($this->getMultiSelectTableName(), 'ms')
			->where(['id' => $entity->id])
			->orderBy(['ac.name' => 'ASC'])
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
			->from("addressbook_contact", 'o')
			->where(['name' => $texts])
			->andWhere(['fieldId' => $this->field->id])
			->orderBy(['o.name' => 'ASC'])
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
			$query->join($this->getMultiSelectTableName(), $joinAlias, $joinAlias.'.id = '.$primaryTableAlias.'.id', 'left');

			if(isset($value[0]) && is_numeric($value[0])) {
				//When field option ID is passed by a saved filter
				$criteria->where($joinAlias. '.contactId', '=', $value);
			} else{
				//for text queries we must join the contacts.
				$alias = 'ac_' . uniqid();
				$query->join('addressbook_contact', $alias, $alias . '.id = '.$joinAlias. '.contactId', 'left');
				$criteria->where($alias . '.name', $comparator, $value);
			}

		});
	}

}
