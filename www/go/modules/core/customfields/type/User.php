<?php

namespace go\modules\core\customfields\type;

use Exception;
use GO;
use go\core\db\Query;
use go\core\db\Utils;

class User extends Base {

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
			$sql = "ALTER TABLE `" . $this->field->tableName() . "` ADD CONSTRAINT `" . $this->field->tableName() . "_ibfk_" . $this->field->id . "` FOREIGN KEY (" . Utils::quoteColumnName($this->field->databaseName) . ") REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";			
			if(!GO()->getDbConnection()->query($sql)) {
				throw new \Exception("Couldn't add contraint");
			}
		}			
		return true;
	}
}
