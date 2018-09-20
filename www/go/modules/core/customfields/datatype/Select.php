<?php

namespace go\modules\core\customfields\datatype;

use Exception;
use GO;
use go\core\db\Query;

class Select extends Base {

	protected function getFieldSQL() {
		$d = $this->field->getDefault();
		$d = isset($d) ? (int) $d :  "NULL";
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
		if(!parent::onFieldSave()) {
			return false;
		}
		
		if(!isset($this->options)) {
			return true;
		}
		
		GO()->getDbConnection()->delete('core_customfields_select_option', (new Query)->where(['fieldId' => $this->field->id]))->execute();
		
		foreach($this->options as $o) {
			$o['fieldId'] = $this->field->id;
			if(!GO()->getDbConnection()->insert('core_customfields_select_option', $o)->execute()) {
				throw new Exception("could not save select option");
			}
		}
		
		return true;
	}
}
