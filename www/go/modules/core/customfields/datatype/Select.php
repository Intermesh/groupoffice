<?php

namespace go\modules\core\customfields\datatype;

use Exception;
use GO;
use go\core\db\Query;
use go\core\db\Utils;

class Select extends Base {

	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) ? (int) $d : "NULL";
		return "int(11) DEFAULT " . $d;
	}

	public function getOptions() {
		return (new Query())
										->select("*")
										->from('core_customfields_select_option')
										->where(['fieldId' => $this->field->id])
										->all();
	}

	private $options;

	public function setOptions(array $options) {
		$this->options = $options;
	}

	public function onFieldSave() {
		if (!parent::onFieldSave()) {
			return false;
		}

		if (!isset($this->options)) {
			return true;
		}

		if ($this->field->isNew()) {
			$sql = "ALTER TABLE `" . $this->field->tableName() . "` ADD CONSTRAINT `" . $this->field->tableName() . "_ibfk_" . $this->field->id . "` FOREIGN KEY (" . Utils::quoteColumnName($this->field->databaseName) . ") REFERENCES `core_customfields_select_option`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";			
			if(!GO()->getDbConnection()->query($sql)) {
				throw new \Exception("Couldn't add contraint");
			}
		}

		$ids = [];
		foreach ($this->options as $o) {

			if (!empty($o['id'])) {
				$ids[] = $o['id'];
			}

			$o['fieldId'] = $this->field->id;
			if (!GO()->getDbConnection()->replace('core_customfields_select_option', $o)->execute()) {
				throw new Exception("could not save select option");
			}
		}

		if (!empty($ids)) {
			GO()->getDbConnection()->delete('core_customfields_select_option', (new Query)
											->where(['fieldId' => $this->field->id])
											->andWhere('id', 'not in', $ids)
			)->execute();
		}



		return true;
	}
	
	public function onFieldDelete() {		
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` DROP FOREIGN KEY " . $this->field->tableName() . "_ibfk_" . $this->field->id;			
		if(!GO()->getDbConnection()->query($sql)) {
			throw new \Excpetion("Couldn't drop foreign key");
		}
			
		return parent::onFieldDelete();
	}

}
