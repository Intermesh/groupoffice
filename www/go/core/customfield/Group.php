<?php

namespace go\core\customfield;

use GO;
use go\core\db\Utils;

class Group extends Base {

	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) ? (int) $d : "NULL";
		return "int(11) DEFAULT " . $d;
	}
	
	public function onFieldSave() {
		if (!parent::onFieldSave()) {
			return false;
		}		

		if ($this->field->isNew()) {
			$this->addConstraint();
		}			
		return true;
	}
	
	//public for migration from 6.3. Make private in 6.5
	public function addConstraint() {
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` ADD CONSTRAINT `" . $this->getConstraintName() . "` FOREIGN KEY (" . Utils::quoteColumnName($this->field->databaseName) . ") REFERENCES `core_group`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";			
		GO()->getDbConnection()->query($sql);
	}
	
	private function getConstraintName() {
		return $this->field->tableName() . "_ibfk_go_" . $this->field->id;
	}
	
	public function onFieldDelete() {		
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` DROP FOREIGN KEY " . $this->getConstraintName();
		if(!GO()->getDbConnection()->query($sql)) {
			throw new \Exception("Couldn't drop foreign key");
		}
			
		return parent::onFieldDelete();
	}
}
