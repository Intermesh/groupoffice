<?php

namespace go\core\customfield;

use Exception;
use GO;
use go\core\db\Query;
use go\core\db\Utils;
use PDOException;

class Select extends Base {

	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) ? (int) $d : "NULL";
		return "int(11) DEFAULT " . $d;
	}

	public function getOptions() {
		return $this->internalGetOptions();
	}

	private $options;

	public function setOptions(array $options) {
		$this->options = $options;
	}
	
	protected function internalGetOptions($parentId = null) {
		$options = (new Query())
										->select("*")
										->from('core_customfields_select_option')
										->where(['fieldId' => $this->field->id, 'parentId' => $parentId])
										->all();
		
		foreach($options as &$o) {
			$o['children'] = $this->internalGetOptions($o['id']);
		}
		
		return $options;		
	}

	public function onFieldSave() {
		if (!parent::onFieldSave()) {
			return false;
		}		

		if ($this->field->isNew()) {
			$this->addConstraint();
		}
		
		$this->saveOptions();	

		return true;
	}
	
	//Is public for migration. Can be made private in 6.5
	public function addConstraint() {
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` ADD CONSTRAINT `" . $this->getConstraintName() . "` FOREIGN KEY (" . Utils::quoteColumnName($this->field->databaseName) . ") REFERENCES `core_customfields_select_option`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";			
		GO()->getDbConnection()->query($sql);
	}
	
	private function getConstraintName() {
		return $this->field->tableName() . "_ibfk_go_" . $this->field->id;
	}
	
	public function dbToText($value, &$values) {
	
		//new model
		if(empty($value)) {
			return [];
		}

		return (new Query())
						->selectSingleValue("text")
						->from('core_customfields_select_option')
						->where(['id' => $value])
						->single();
	}
	
	protected function saveOptions() {
		
		if (!isset($this->options)) {
			return true;
		}
		$this->savedOptionIds = [];
		$this->internalSaveOptions($this->options);				
		
		$query  = (new Query)->where(['fieldId' => $this->field->id]);
		if (!empty($this->savedOptionIds)) {	 
			 $query->andWhere('id', 'not in', $this->savedOptionIds);
		}
		$deleteCmd = GO()->getDbConnection()->delete('core_customfields_select_option', $query)->execute();
		
		$this->options = null;
	}
	
	protected $savedOptionIds = [];
	
	protected function internalSaveOptions($options, $parentId = null) {
		
		foreach ($options as $o) {

			$o['parentId'] = $parentId;
			$o['fieldId'] = $this->field->id;
			
			$children = $o['children'] ?? [];
			unset($o['children']);
			
			
			if(empty($o['id'])) {
				if (!GO()->getDbConnection()->insert('core_customfields_select_option', $o)->execute()) {
					throw new Exception("could not save select option");
				}
				$o['id'] = GO()->getDbConnection()->getPDO()->lastInsertId();
			} else{
				if (!GO()->getDbConnection()->update('core_customfields_select_option', $o, ['id' => $o['id']])->execute()) {
					throw new Exception("could not save select option");
				}
			}
			
			$this->savedOptionIds[] = $o['id'];
			
			$this->internalSaveOptions($children, $o['id']);
		}
	}
	
	public function onFieldDelete() {		
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` DROP FOREIGN KEY " . $this->getConstraintName();
		if(!GO()->getDbConnection()->query($sql)) {
			throw new \Exception("Couldn't drop foreign key");
		}
			
		return parent::onFieldDelete();
	}

}
