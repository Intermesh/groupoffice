<?php

namespace go\core\customfield;

use GO;
use go\core\db\Criteria;
use go\core\db\Table;
use go\core\model\FieldSet;
use go\core\orm\CustomFieldsModel;
use go\core\orm\Entity;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Query;

class MultiSelect extends Select {
	
	protected $optionsToSave;
	
	protected function getFieldSQL() {
		return false;
	}

	/**
	 * @return Table the table definition of the Entity the fieldset of this
	 * customfield belongs to.
	 */
	protected function getTableDefinition() {
		$fieldSet = FieldSet::findById($this->field->fieldSetId);
		$entityType = EntityType::findByName($fieldSet->getEntity());
		$cls = $entityType->getClassName();

		if(is_a($cls, \GO\Base\Db\ActiveRecord::class, true)) {
			$table = go()->getDatabase()->getTable($cls::model()->tableName());
		} else {
			$table = $cls::getMapping()->getPrimaryTable();
		}
		return $table;
	}

	public function onFieldSave(): bool
	{

		if ($this->field->isNew()) {
			$this->createMultiSelectTable();
		}

		$this->saveOptions();

		return true;
	}

	public function getMultiSelectTableName(): string
	{
		return "core_customfields_multiselect_" . $this->field->id;
	}

	//Is public for migration. Can be made private in 6.5
	public function createMultiSelectTable() {

		$tableName = $this->field->tableName();
		$multiSelectTableName = $this->getMultiSelectTableName();
		$entityColumn = $this->getTableDefinition()->getColumn('id');
		$type = $entityColumn->dataType . ($entityColumn->unsigned?' UNSIGNED':'');

		$sql = "CREATE TABLE IF NOT EXISTS `$multiSelectTableName` (
			`id` $type NOT NULL,
			`optionId` int(11) NOT NULL,
			PRIMARY KEY (`id`,`optionId`),
			KEY `optionId` (`optionId`)
		) ENGINE=InnoDB;";

		if(!go()->getDbConnection()->query($sql)) {
			return false;
		}

		$sql = "ALTER TABLE `" . $multiSelectTableName . "`
			ADD CONSTRAINT `" . $multiSelectTableName . "_ibfk_1` FOREIGN KEY (`id`) REFERENCES `" . $tableName . "` (`id`) ON DELETE CASCADE,
		  ADD CONSTRAINT `" . $multiSelectTableName . "_ibfk_2` FOREIGN KEY (`optionId`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE CASCADE;";

		return go()->getDbConnection()->query($sql);
	}
	
	
	public function beforeSave($value, \go\core\orm\CustomFieldsModel $model, $entity, &$record): bool
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
			if(!go()->getDbConnection()->replace($this->getMultiSelectTableName(), ['id' => $entity->id, 'optionId' => $optionId])->execute()) {
				return false;
			}
		}
		
		
		$query  = (new Query)->where(['id' => $entity->id]);
		if (!empty($this->optionsToSave)) {	 
			 $query	->andWhere('optionId', 'not in', $this->optionsToSave);
		}
		
		if(!go()->getDbConnection()->delete($this->getMultiSelectTableName(), $query)->execute()) {
			return false;
		}
		
		$this->optionsToSave = null;
		
		return true;
	}

	public function dbToApi($value, \go\core\orm\CustomFieldsModel $values, $entity) {
		
		//new model
		if($entity->isNew()) {
			return [];
		}

		return (new Query())
						->selectSingleValue("optionId")
						->from($this->getMultiSelectTableName())
						->where(['id' => $entity->id])
						->all();
	}

	public function dbToText($value, \go\core\orm\CustomFieldsModel $values, $entity)
	{
		//new model
		if($entity->isNew()) {
			return "";
		}

		return implode(", ", (new Query())
							->selectSingleValue("o.text")
							->join("core_customfields_select_option", "o", "o.id = ms.optionId")
							->from($this->getMultiSelectTableName(), 'ms')
							->where(['id' => $entity->id])
							->orderBy(['o.sortOrder' => 'ASC'])
							->all());
	}

	public function textToDb($value, \go\core\orm\CustomFieldsModel $values, $entity)
	{	
		if(empty($value)) {
			return [];
		}

		$texts = array_map('trim', explode(',', $value));

		$ids = (new Query())
							->selectSingleValue("id")							
							->from("core_customfields_select_option", 'o')
							->where(['text' => $texts])
							->andWhere(['fieldId' => $this->field->id])
							->orderBy(['o.sortOrder' => 'ASC'])
							->all();

		if(count($ids) != count($texts)) {
			throw new \Exception("Invalid value(s) for multi select field '". $this->field->databaseName . "': ".implode(', ', $texts));
		}
		return $ids;
	}

	public function onFieldDelete(): bool
	{
		return go()->getDbConnection()->query("DROP TABLE IF EXISTS `" . $this->getMultiSelectTableName() . "`;")->execute();
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
	public function defineFilter(Filters $filters) {
		
		
		$filters->addText($this->field->databaseName, function(Criteria $criteria, $comparator, $value, Query $query, array $filter){
			
			$cls = $query->getModel();
			$primaryTableAlias = array_values($cls::getMapping()->getTables())[0]->getAlias();
			$joinAlias = $this->getJoinAlias();
			$query->join($this->getMultiSelectTableName(), $joinAlias, $joinAlias.'.id = '.$primaryTableAlias.'.id', 'left');

			if(isset($value[0]) && is_numeric($value[0])) {
				//When field option ID is passed by a saved filter
				$criteria->where($joinAlias. '.optionId', '=', $value);
			} else{
				//for text queries we must join the options.
				$alias = 'opt_' . uniqid();
				$query->join('core_customfields_select_option', $alias, $alias . '.id = '.$joinAlias. '.optionId', 'left');
				$criteria->where($alias . '.text', $comparator, $value);
			}

		});
	}

}
