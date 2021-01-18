<?php

namespace go\core\customfield;

use Exception;
use GO;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\db\Utils;
use go\core\ErrorHandler;
use go\core\orm\Filters;
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

	public function isModified()
	{
		return isset($this->options);
	}

	protected function internalGetOptions($parentId = null) {
		$options = (new Query())
										->select("*")
										->from('core_customfields_select_option')
										->where(['fieldId' => $this->field->id, 'parentId' => $parentId])
										->all();
		
		foreach($options as &$o) {
			$o['enabled'] = !!$o['enabled'];
			$o['children'] = $this->internalGetOptions($o['id']);
		}
		
		return $options;		
	}

	public function onFieldSave() {

		if ($this->field->isModified('databaseName') && !$this->field->isNew()) {
			$this->dropConstraint();
		}

		if (!parent::onFieldSave()) {
			return false;
		}		

		if ($this->field->isNew() || $this->field->isModified('databaseName')) {
			$this->addConstraint();
		}
		
		$this->saveOptions();	

		return true;
	}
	
	//Is public for migration. Can be made private in 6.5
	public function addConstraint() {
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` ADD CONSTRAINT `" . $this->getConstraintName() . "` FOREIGN KEY (" . Utils::quoteColumnName($this->field->databaseName) . ") REFERENCES `core_customfields_select_option`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;";			
		return go()->getDbConnection()->exec($sql);
	}

	private function dropConstraint() {
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` DROP CONSTRAINT `" . $this->getConstraintName() . "`;";
		return go()->getDbConnection()->exec($sql);
	}

	private function getConstraintName()
	{
		$strName = $this->field->tableName() . "_ibfk_go_" . $this->field->id;
		if (strlen($strName) > 64) { // Constraint names are restricted to 64 characters!
			$strName = str_replace('_custom_fields_', '_cf_', $strName);
		}
		return $strName;
	}
	
	public function dbToText($value, \go\core\orm\CustomFieldsModel $values, $entity) {

		if(empty($value)) {
			return "";
		}

		return (new Query())
						->selectSingleValue("text")
						->from('core_customfields_select_option')
						->where(['id' => $value])
						->single();
	}

	public function textToDb($value, \go\core\orm\CustomFieldsModel $values, $entity) {

		if(empty($value)) {
			return null;
		}

		$id = (new Query())
						->selectSingleValue("id")
						->from('core_customfields_select_option')
						->where(['text' => $value])
						->andWhere(['fieldId' => $this->field->id])
						->single();

		if(!$id) {
			throw new \Exception("Invalid select option text for field '".$this->field->databaseName."': ". $value);
		}

		return $id;
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
		$deleteCmd = go()->getDbConnection()->delete('core_customfields_select_option', $query)->execute();
		
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
				if (!go()->getDbConnection()->insert('core_customfields_select_option', $o)->execute()) {
					throw new Exception("could not save select option");
				}
				$o['id'] = go()->getDbConnection()->getPDO()->lastInsertId();
			} else{
				if (!go()->getDbConnection()->update('core_customfields_select_option', $o, ['id' => $o['id']])->execute()) {
					throw new Exception("could not save select option");
				}
			}
			
			$this->savedOptionIds[] = $o['id'];
			
			$this->internalSaveOptions($children, $o['id']);
		}
	}
	
	public function onFieldDelete() {		
		$sql = "ALTER TABLE `" . $this->field->tableName() . "` DROP FOREIGN KEY " . $this->getConstraintName();

		try {
      go()->getDbConnection()->query($sql);
    }catch (Exception $e) {
		  ErrorHandler::logException($e);
		  //ignore so we can continue with delete
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
				$query->join(
					'core_customfields_select_option',
					$alias, $alias . '.id = customFields.' .
					$this->field->databaseName,
				'left');
				$criteria->where($alias . '.text', $comparator, $value);
			}
		});
	}

}
