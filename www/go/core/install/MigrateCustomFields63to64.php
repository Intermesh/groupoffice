<?php
namespace go\core\install;

use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\model\Field;
use go\core\model\Group;
use go\core\model\User;
use go\core\orm\EntityType;
use PDOException;
use function GO;

class MigrateCustomFields63to64 {	
	
	const MISSING_PREFIX = '** Missing ** ';
	
	public function migrateEntity($entityName) {
		
		echo "Migrating custom fields for entity: " . $entityName ."\n";
		
		$entityType = EntityType::findByName($entityName);
		
		if(!$entityType) {
			echo "Entity type: ". $entityName . " not found. Skipping.\n";
			return;
		}
		
		$fields = Field::findByEntity($entityType->getId());

		foreach ($fields as $field) {
			
			echo $field->id . ' - '.$field->type ."\n";
			
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
				
				case "User":
					$this->updateSelectEntity($field, User::class);
					break;
				
				case "Group":
					$this->updateSelectEntity($field, Group::class);
					break;
				
				case "Textarea":
					$field->type = "TextArea";
					$field->save();
					break;
				
				case "Datetime":
					$field->type = "DateTime";
					$field->save();
					break;
				
				case "Heading":
				case "Infotext":
				case "ReadonlyText":
					$field->type = "Notes";
					$field->setOption("formNotes", $field->name);
					$field->save();
					break;
				
			}
		}
		
//		exit("STOP FOR TEST");
	}
	
	public function updateSelectEntity(Field $field, $entityCls, $incrementID = 0) {		
		
		$query = $this->findRecords($field);		
		foreach($query as $record) {
			//Value is string <id>:<Text>
			$id = explode(':', $record[$field->databaseName])[0];
			
			GO()->getDbConnection()
								->update(
												$field->tableName(), 
												[$field->databaseName => $id + $incrementID],
												['id' => $record['id']]
												)->execute();
		}
		
		$validIds = $entityCls::find()->selectSingleValue('id');
		
		//for changing db column
		$field->setDefault(null);
		if(!$field->save()) {
			throw new \Exception("Couldn't save field: ".var_export($field->getValidationErrors()));
		}
		
		//nullify invalid records
		GO()->getDbConnection()->update(
						$field->tableName(), 
						[$field->databaseName => null],
						(new Query)->where($field->databaseName, 'NOT IN', $validIds)
						)->execute();
		
		try {			
			$field->getDataType()->addConstraint();
		} catch(PDOException $e) {			
			//ignore duplicates
		}
	}
	
	private function findRecords(Field $field) {
		return GO()->getDbConnection()->select("id, `" . $field->databaseName . "`")
						->from($field->tableName())
						->where($field->databaseName, '!=', "")
						->andWhere($field->databaseName, 'IS NOT', null);
	}
	
	
	public function convertTypeNames() {
		
		$fields = Field::find();
		
		foreach ($fields as $field) {
			$parts = explode('\\', $field->type);
			$type = array_pop($parts);
			
			if($type == "UserGroup") {
				$type = "Group";
			}
			
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
		$this->insertMissingOptions($field);
		
		$selectOptions = $field->getDataType()->getOptions();		
		
		foreach($selectOptions as $o) {
			$updateFilter = new Query();
			$updateFilter->where("trim(`".$field->databaseName."`) = :text")->bind(":text", trim($o['text']));
			
			if(substr($o['text'], 0, strlen(self::MISSING_PREFIX)) == self::MISSING_PREFIX) {
				$strWithoutMissing = substr($o['text'], strlen(self::MISSING_PREFIX));
				$updateFilter->orWhere($field->databaseName,'=' ,trim($strWithoutMissing));
			}
			$updateQ = GO()->getDbConnection()
				->update($field->tableName(), [$field->databaseName => $o['id']], $updateFilter);

			$updateQ->execute();
		}
		
		//for changing db column
		$field->setDefault(null);
		$field->save();
		
		$this->nullifyInvalidOptions($field);
		try {			
			$field->getDataType()->addConstraint();
		} catch(PDOException $e) {			
			//ignore duplicates
		}
	}
	
	private function updateMultiSelect(Field $field) {
		$field->type = "MultiSelect";		
		
		$this->insertMissingOptions($field, true);
		try{
			$field->getDataType()->createMultiSelectTable();
		}catch(\PDOException $e) {
			//ignore already existing
		}
		
		
		$options = GO()->getDbConnection()
						->select("*")
						->from("core_customfields_select_option")
						->where('fieldId', '=', $field->id)->all();
		
		foreach($options as $o) {
			$optionMap[trim($o['text'])] = $o['id'];
		}
		
		$query = $this->findRecords($field);
		
		foreach($query as $record){
			$values = explode("|", $record[$field->databaseName]);
			
			foreach($values as $value) {
				$value = trim($value);
				if(empty($value)) {
					continue;
				}

				if(!isset($optionMap[$value]) && !isset($optionMap[self::MISSING_PREFIX.$value])) {					
					echo "ERROR: Invalid select option '" . $value . "' for field ". $field->id .' record ID: '. $record['id'];
					continue;
				}
				
				$valueToSet = isset($optionMap[$value]) ? $optionMap[$value] : $optionMap[self::MISSING_PREFIX.$value];					
				
				GO()->getDbConnection()
								->replace(
												$field->getDataType()->getMultiSelectTableName(), 
												['id' => $record['id'], 'optionId' => $valueToSet]
												)->execute();
			}
		}		
		$field->save();
		
		//remove column because it's stored in linking table
		$sql = "ALTER TABLE `" . $field->tableName() . "` DROP " . Utils::quoteColumnName($field->databaseName) ;
		GO()->getDbConnection()->query($sql);
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
		$parts = explode(':', $v);
		
		if(count($parts) < 1){
			return null;
		}
		
		$id = (int) $parts[0];
		if(!$id) {
			return null;
		}
		
		$text = trim($parts[1]);
		if(empty($text)) {
			return null;
		}

		//Check if text exists in 
		$existsQ = GO()->getDbConnection()
				->selectSingleValue('id')
				->from("core_customfields_select_option")
				->where('id', '=', $id + self::TREE_SELECT_OPTION_INCREMENT);

		$exists = $existsQ->single();
		
		if(!$exists){
			$data = [
				'id'=>$id + self::TREE_SELECT_OPTION_INCREMENT,
				'fieldId'=>$fields[0]->id,
				'parentId'=>NULL,
				'text'=>self::MISSING_PREFIX.$text
			];
			
			$insertQ = GO()->getDbConnection()->insert("core_customfields_select_option", $data);
			$insertQ->execute();
		}
		
		return $id + self::TREE_SELECT_OPTION_INCREMENT;
	}
	
	private function findTreeSelectRecords(Field $field, array $fields) {
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
									'text' => $o['name']							
							])->execute();
		}
	}
	
	private function updateTreeSelect(Field $field) {			
		
		$this->convertTreeSelectOptions($field);
		
		$fields = $this->findSlaveFields($field);
		foreach($this->findTreeSelectRecords($field, $fields) as $record) {			
			
			GO()->debug($record);
			
			$id = $this->findSelectOptionId($record, $fields);			
			
			GO()->debug($id);
			
			GO()->getDbConnection()
							->update(
											$field->tableName(),
											[$field->databaseName => $id], 
											['id' => $record['id']]
											)->execute();
		}		
		
		$field->type = "Select";
		$field->setDefault(null);
		$field->save();
		
		$this->nullifyInvalidOptions($field);
		try {
			$field->getDataType()->addConstraint();
		} catch(PDOException $e) {			
			//ignore duplicates
		}
		
		//delete slaves
		array_shift($fields);
		foreach($fields as $field) {
			$field->type = "Text";
			if(!$field->delete()) {
				throw new \Exception("Could not delete tree select slave");
			}
		}
	}
	
	
	
	private function insertMissingOptions(Field $field, $multiselect = false) {
		//set invalid options to null
		$optionTexts = GO()->getDbConnection()
						->selectSingleValue('text')
						->from("core_customfields_select_option")
						->where('fieldId', '=', $field->id);
		
		$missingQuery = GO()->getDbConnection()
						->selectSingleValue('`'.$field->databaseName.'`')->distinct()
						->from($field->tableName())						
						->where($field->databaseName, 'NOT IN', $optionTexts);

		$missing = $missingQuery->all();

		if($multiselect) {
			$m = [];
			foreach($missing as $msv) {
				if(!empty($msv)) {
					$m = array_merge($m,explode('|', $msv));
				}
			}
			$missing = $m;
		}

		$missing = array_unique(array_map('trim', $missing));

		$optionTexts = array_map('trim', $optionTexts->all());
		
		$missing = array_filter($missing, function($text) use ($optionTexts) {
			return !empty($text) && !in_array($text, $optionTexts);
		});
		
		$data = array_map(function($text) use ($field) {
			return [
					"text" => self::MISSING_PREFIX.$text,
					"fieldId" => $field->id
			];
		}, $missing);
		
		if(empty($data)){
			return;
		}
		
		foreach($data as $insertRecord){
			$insertQ = GO()->getDbConnection()->insert("core_customfields_select_option", $insertRecord);
			$insertQ->execute();
		}				
	}
	
	private function nullifyInvalidOptions(Field $field) {
		//set invalid options to null
		$optionIds = GO()->getDbConnection()
						->selectSingleValue('id')
						->from("core_customfields_select_option")
						->where('fieldId', '=', $field->id);
		
//		GO()->getDbConnection()->update(
//						$field->tableName(), 
//						[$field->databaseName => null], 
//						(new Query)
//						->where($field->databaseName, 'NOT IN', $optionIds)
//						)->execute();
		
				GO()->getDbConnection()->update(
						$field->tableName(), 
						[$field->databaseName => null], 
						(new Query)
						->where($field->databaseName, '=' , "")
						)->execute();
				
		$query = GO()->getDbConnection()
						->selectSingleValue('`'.$field->databaseName.'`')
						->from($field->tableName())
						->where($field->databaseName, 'NOT IN', $optionIds)->andWhereNot([$field->databaseName => null]);

		if(($missing = $query->single())) {
			throw new \Exception("Field ". $field->id ." has invalid data '$missing'. No select option found.");
		}
	}
}
