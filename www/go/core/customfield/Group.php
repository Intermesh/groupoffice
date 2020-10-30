<?php

namespace go\core\customfield;

use GO;
use go\core\db\Criteria;
use go\core\db\Utils;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Query;

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
		go()->getDbConnection()->query($sql);
	}
	
	private function getConstraintName() {
		return $this->field->tableName() . "_ibfk_go_" . $this->field->id;
	}
	
	public function onFieldDelete() {		
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` DROP FOREIGN KEY " . $this->getConstraintName();
		if(!go()->getDbConnection()->query($sql)) {
			throw new \Exception("Couldn't drop foreign key");
		}
			
		return parent::onFieldDelete();
	}


	/**
	 * Defines an entity filter for this field.
	 * 
	 * @see Entity::defineFilters()
	 * @param Filters $filter
	 */
	public function defineFilter(Filters $filters) {		
		
		$filters->addText($this->field->databaseName, function(Criteria $criteria, $comparator, $value, Query $query, array $filter){
			$this->joinCustomFieldsTable($query);	
			
			if(isset($value[0]) && is_numeric($value[0])) {
				//When field option ID is passed by a saved filter
				$criteria->where('customFields.' . $this->field->databaseName, '=', $value);
			} else{
				//for text queries we must join the options.
				$alias = 'opt_' . $this->field->id;
				$query->join('core_group', $alias, $alias . '.id = customFields.' . $this->field->databaseName, 'LEFT');
				$criteria->where($alias . '.name', $comparator, $value);
			}
		});
	}


	public function dbToText($value, \go\core\orm\CustomFieldsModel $values, $entity) {

		if(empty($value)) {
			return "";
		}

		return (new \go\core\db\Query())
			->selectSingleValue("name")
			->from('core_group')
			->where(['id' => $value])
			->single();
	}

	public function textToDb($value, \go\core\orm\CustomFieldsModel $values, $entity) {

		if(empty($value)) {
			return null;
		}

		$id = \go\core\model\Group::find(['id'])
			->selectSingleValue("g.id")
			->where(['name' => $value])
			->filter(['permissionLevel' => Acl::LEVEL_READ])
			->single();

		return $id;
	}
}
