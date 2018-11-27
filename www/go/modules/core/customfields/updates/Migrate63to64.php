<?php
namespace go\modules\core\customfields\updates;

use go\core\db\Query;
use go\modules\core\customfields\model\Field;
use go\modules\core\customfields\model\FieldSet;
use PDOException;
use function GO;

class Migrate63to64 {
	public function run() {
		
		$this->convertTypeNames();
		
		$fields = Field::find();

		foreach ($fields as $field) {
			
			$fs = FieldSet::findById($field->fieldSetId);
			
			//skip contacts
			if($fs->getEntity() == "Contact" || $fs->getEntity() == "Company") {
				continue;
			}
			
			switch ($field->type) {
				case "Select":
						if($field->getOption('multiselect')) {
							$this->updateMultiSelect($field);
						} else
						{
							$this->updateSingleSelect($field);
						}
					break;

				case "Treeselect":
						$this->updateTreeSelect($field);
					break;
			}
		}
		
		//exit("STOP FOR TEST");
	}
	
	
	private function convertTypeNames() {
		
		$fields = Field::find();
		
		foreach ($fields as $field) {
			$parts = explode('\\', $field->type);
			$type = array_pop($parts);
			
			//Use DBAL because entity will alter database and we don't need that here.
			GO()->getDbConnection()
							->update(
											'core_customfields_field', 
											['type' => $type], 
											['id' => $field->id]
											)->execute();
		}
	}
	
	private function updateSingleSelect(Field $field) {
		$selectOptions = $field->getDataType()->getOptions();		
		
		foreach($selectOptions as $o) {			
			GO()->getDbConnection()
							->update($field->tableName(), [$field->databaseName => $o['id']], [$field->databaseName => $o['text']])->execute();	
		}		
		
		$optionIds = GO()->getDbConnection()
						->selectSingleValue('id')
						->from("core_customfields_select_option")
						->where('fieldId', '=', $field->id);
	
		GO()->getDbConnection()->update(
						$field->tableName(), 
						[$field->databaseName => null], 
						(new Query)
						->where($field->databaseName, 'NOT IN', $optionIds)
						)->execute();
		
		//for changing db column
		$field->save();
		try {
			$field->getDataType()->addConstraint();
		} catch(PDOException $e) {			
			//ignore duplicates
		}
	}
	
	private function updateMultiSelect(Field $field) {
		
	}
	
	
	private function findSlaveFields(Field $field) {
		$allSlaves = Field::find()->where(['type' => 'TreeselectSlave'])->all();
		$treeSlaves = array_filter($allSlaves, function($slave) use ($field) {
			return $slave->getOption('treeMasterFieldId') == $field->id;
		});
		
		$fields[0] = $field;		
		foreach($treeSlaves as $slave) {
			$fields[$slave->getOption("nestingLevel")] = $slave;
		}
		ksort($fields);
		
		return $fields;
	}
	
	
	const TREE_SELECT_OPTION_INCREMENT = 100000;
	
	/**
	 * 
	 * @param type $record
	 * @param Field[] $fields
	 */
	private function findSelectOptionId($record, array $fields) {
		//find value with highest nesting level
		$v = null;
		foreach($fields as $field) {
			if(!empty($record[$field->databaseName])) {
				$v = $record[$field->databaseName];
			}
		}

		//Value is string <id>:<Text>
		$id = explode(':', $v)[0];
		
		return $id + self::TREE_SELECT_OPTION_INCREMENT;
	}
	
	private function findRecords(Field $field, array $fields) {
		$query = GO()->getDbConnection()->select()
						->from($field->tableName());		
		foreach($fields as $field) {
			$query->orWhere($field->databaseName, '!=', "");
		}
		
		return $query;
	}
	
	private function convertTreeSelectOptions(Field $field) {
		$ids = GO()->getDbConnection()->selectSingleValue('id')->from("cf_tree_select_options")->all();
		$ids[] = "0";
		
		$oldOptions = GO()->getDbConnection()
						->select()
						->from('cf_tree_select_options')
						->where('field_id', '=', $field->id)
						->andWhere('parent_id', 'IN', $ids)
						->orderBy(['parent_id'=>'ASC']);
		
		foreach($oldOptions as $o) {
			GO()->getDbConnection()
							->insertIgnore("core_customfields_select_option", [
									'id' => $o['id'] + self::TREE_SELECT_OPTION_INCREMENT,
									'fieldId' => $field->id,
									'parentId' => !empty($o['parent_id']) ? $o['parent_id'] + self::TREE_SELECT_OPTION_INCREMENT : null,
									'text' => $o['name'],
									'sortOrder' => $o['sort']									
							])->execute();
		}
	}
	
	private function updateTreeSelect(Field $field) {		
		
		$this->convertTreeSelectOptions($field);
		
		$fields = $this->findSlaveFields($field);
		foreach($this->findRecords($field, $fields) as $record) {			
			$id = $this->findSelectOptionId($record, $fields);			
			
			GO()->getDbConnection()
							->update(
											$field->tableName(),
											[$field->databaseName => $id], 
											['id' => $record['id']]
											)->execute();
		}
		
		$field->type = "Select";
		$field->save();
		try {
			$field->getDataType()->addConstraint();
		} catch(PDOException $e) {			
			//ignore duplicates
		}
		
		//delete slaves
		array_shift($fields);
		foreach($fields as $field) {
			$field->type = "Text";
			$field->delete();
		}
	}
}
