<?php

namespace go\modules\community\addressbook\customfield;

use GO;
use go\core\db\Utils;
use go\core\customfield\Base;
use go\core\db\Criteria;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Query;
use go\modules\community\addressbook\model;

class Contact extends Base {

	
	public function getModelClass()
	{
		return model\Contact::class;
	}
	
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
	
	public function addConstraint() {
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` ADD CONSTRAINT `" . $this->getConstraintName() . "` FOREIGN KEY (" . Utils::quoteColumnName($this->field->databaseName) . ") REFERENCES `addressbook_contact`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";			
		go()->getDbConnection()->query($sql);
	}

	private function getConstraintName()
	{
		$strName = $this->field->tableName() . "_ibfk_go_" . $this->field->id;
		if (strlen($strName) > 64) { // Constraint names are restricted to 64 characters!
			$strName = str_replace('_custom_fields_', '_cf_', $strName);
		}
		return $strName;
	}
	
	public function onFieldDelete() {

		try {
			$sql = "ALTER TABLE `" . $this->field->tableName() . "` DROP FOREIGN KEY " . $this->getConstraintName();
			if (!go()->getDbConnection()->query($sql)) {
				throw new \Exception("Couldn't drop foreign key");
			}
		} catch(\PDOException $e) {

			//ignore
			go()->getDebugger()->warn($e);
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
				$query->join('addressbook_contact', $alias, $alias . '.id = customFields.' . $this->field->databaseName, 'LEFT');
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
			->from('addressbook_contact')
			->where(['id' => $value])
			->single();
	}

	public function textToDb($value, \go\core\orm\CustomFieldsModel $values, $entity) {

		if(empty($value)) {
			return null;
		}

		$id = model\Contact::find(['id'])
			->selectSingleValue("c.id")
			->filter(['permissionLevel' => Acl::LEVEL_READ])
			->where(['name' => $value])
			->single();

		if(!$id) {
			$contact = new model\Contact();
			$contact->isOrganization = $this->field->getOption('isOrganization');
			$contact->allowNew = $this->field->getOption('allowNew');
			$contact->name = $contact->lastName = $value;
			$contact->addressBookId = go()->getAuthState()->getUser(['addressBookSettings'])->addressBookSettings->getDefaultAddressBookId();
			if(!$contact->save()) {
				throw new \Exception("Could not save contact: " . $contact->getValidationErrorsAsString());
			}

			$id = $contact->id;
		}

		return $id;
	}
}

