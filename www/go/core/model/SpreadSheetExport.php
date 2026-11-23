<?php

namespace go\core\model;

use go\core\db\Criteria;
use go\core\ErrorHandler;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\util\JSON;

class SpreadSheetExport extends Entity
{
	public ?string $id;
	public string $userId;
	public string $name;
	protected $columns;
	protected $entityTypeId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_spreadsheet_export", "export");
	}

	protected function canCreate(): bool
	{
		return true;
	}

	/**
	 * Saved exports belong to a user. Others may use them but not change them.
	 *
	 * @return int
	 */
	protected function internalGetPermissionLevel(): int
	{
		if(!go()->getAuthState() || !go()->getAuthState()->isAuthenticated()) {
			return 0;
		}
		if(go()->getAuthState()->isAdmin()) {
			return Acl::LEVEL_MANAGE;
		}
		// When creating, the user must be the current user. Also when the user is changed.
		$userIds = [$this->userId ?? null];
		if(!$this->isNew() && $this->isModified('userId')) {
			$userIds[] = $this->getOldValue('userId');
		}
		foreach($userIds as $userId) {
			if($userId != go()->getAuthState()->getUserId()) {
				return $this->isNew() || $this->isModified('userId') ? 0 : Acl::LEVEL_READ;
			}
		}
		return Acl::LEVEL_MANAGE;
	}

	protected static function defineFilters(): Filters
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