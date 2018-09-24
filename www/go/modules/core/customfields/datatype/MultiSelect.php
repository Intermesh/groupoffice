<?php

namespace go\modules\core\customfields\datatype;

use Exception;
use GO;
use go\core\db\Query;
use go\core\db\Utils;

class MultiSelect extends Select {
	
	private $optionsToSave;

	public function onFieldSave() {
		if (!parent::onFieldSave()) {
			return false;
		}

		if ($this->field->isNew()) {
			$this->createMultiSelectTable();
		}

		$this->saveOptions();

		return true;
	}

	private function getMultiSelectTableName() {
		return "core_customfields_multiselect_" . $this->field->id;
	}

	private function createMultiSelectTable() {

		$tableName = $this->field->tableName();

		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->getMultiSelectTableName() . "` (
			`id` int(11) NOT NULL,
			`optionId` int(11) NOT NULL,
			PRIMARY KEY (`id`,`optionId`),
			KEY `optionId` (`optionId`)
		) ENGINE=InnoDB;";

		if(!GO()->getDbConnection()->query($sql)) {
			return false;
		}

		$sql = "ALTER TABLE `" . $this->getMultiSelectTableName() . "`
			ADD CONSTRAINT `" . $this->getMultiSelectTableName() . "_ibfk_1` FOREIGN KEY (`id`) REFERENCES `" . $this->field->tableName() . "` (`id`) ON DELETE CASCADE,
		  ADD CONSTRAINT `" . $this->getMultiSelectTableName() . "_ibfk_2` FOREIGN KEY (`optionId`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE CASCADE;";

		return GO()->getDbConnection()->query($sql);
	}
	
	
	public function beforeSave($value, &$values) {
		
		//remove options from record to be inserted and save them for the afterSave method.
		$this->optionsToSave = $value;
		unset($values[$this->field->databaseName]);
		return true;
	}
	
	public function afterSave($value, &$values) {
		
		if(!isset($this->optionsToSave)) {
			return true;
		}
		
		foreach($this->optionsToSave as $optionId) {
			if(!GO()->getDbConnection()->replace($this->getMultiSelectTableName(), ['id' => $values['id'], 'optionId' => $optionId])->execute()) {
				return false;
			}
		}
		
		
		$query  = (new Query)->where(['id' => $values['id']]);
		if (!empty($this->optionsToSave)) {	 
			 $query	->andWhere('optionId', 'not in', $this->optionsToSave);
		}
		
		if(!GO()->getDbConnection()->delete($this->getMultiSelectTableName(), $query)->execute()) {
			return false;
		}
		
		$this->optionsToSave = null;
		
		return true;
	}

	public function dbToApi($value, &$values) {
		
		//new model
		if(empty($values['id'])) {
			return [];
		}

		return array_map(function($i){return (int) $i;}, (new Query())
										->selectSingleValue("optionId")
										->from($this->getMultiSelectTableName())
										->where(['id' => $values['id']])
										->all());
	}

	public function onFieldDelete() {
		return GO()->getDbConnection()->query("DROP TABLE IF EXISTS `" . $this->getMultiSelectTableName() . "`;");
	}

}
