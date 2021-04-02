<?php

namespace go\core\model;

use go\core\db\Criteria;
use go\core\ErrorHandler;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\util\JSON;

class SpreadSheetExport extends Entity
{
	public $id;
	public $userId;
	public $name;
	protected $columns;
	protected $entityTypeId;

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable("core_spreadsheet_export", "export");
	}

	protected static function defineFilters()
	{
		return parent::defineFilters()
			->add("userId", function(Criteria $criteria, $userId) {
				$criteria->andWhere('userId', '=', $userId);
			})
			->add("entity", function(Criteria $criteria, $name) {
				$entityType = EntityType::findByName($name);
				$criteria->andWhere('entityTypeId', '=', $entityType->getId());
			});
	}

	public function getEntity() {
		$entityType = EntityType::findById($this->entityTypeId);
		return $entityType->getName();
	}

	public function setEntity($name) {
		$entityType = EntityType::findByName($name);
		$this->entityTypeId = $entityType->getId();
	}

	public function getColumns() {
		try {
			return empty($this->columns) ? [] : JSON::decode($this->columns);
		} catch(\Exception $e) {
			ErrorHandler::logException($e);
			return [];
		}
	}

	public function setColumns(array $columns) {
		$this->columns = JSON::encode($columns);
	}
}