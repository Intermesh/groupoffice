<?php

namespace go\modules\community\calendar\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\model\Module;
use go\core\orm\Filters;
use go\core\orm\Mapping;

/**
 * Category model
 */
class Category extends Entity {

	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var int could be NULL for global categories */
	public $ownerId;

	/** @var int when not null this category is only visible when the tasklist is selected (no ACL checking allowed)  */
	public $calendarId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("calendar_category", "category");
	}

	protected function init()
	{
		parent::init();

		if($this->isNew())  {
			$this->ownerId = go()->getUserId();
		}
	}

	protected function internalGetPermissionLevel(): int
	{
		if(empty($this->calendarId) && empty($this->ownerId)) {
			return Module::findByName('community', 'calendar')
				->getUserRights()
				->mayChangeCategories ? Acl::LEVEL_MANAGE : Acl::LEVEL_READ;
		}

		if(isset($this->calendarId)) {
			$calendar = Calendar::findById($this->calendarId);

			return $calendar->getPermissionLevel() >= Acl::LEVEL_MANAGE ? Acl::LEVEL_DELETE : Acl::LEVEL_READ;
		} else {
			return $this->ownerId == go()->getUserId() ? Acl::LEVEL_MANAGE : Acl::LEVEL_READ;
		}
	}

	public function getMyRights() {
		$lvl = $this->getPermissionLevel();
		return [
			'mayRead' => $lvl >= 10,
			'mayAdmin' => $lvl >= 50
		];
	}

	public static function getClientName(): string
	{
		return "CalendarCategory";
	}

	protected static function textFilterColumns(): array
	{
		return ['name'];
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('ownerId', function(Criteria $criteria, $value) {
				$criteria->where('ownerId', '=', $value)
					->andWhere('calendarId' , '=', null);
			})
			->add('calendarId', function(Criteria $criteria, $value) {
				$criteria->where('calendarId', '=', $value);
			})->add('name', function(Criteria $criteria, $value) {
				$criteria->where('name', 'LIKE', '%'.$value.'%');
			})
			->add('global', function(Criteria $criteria, $value) {
				$op = $value ? '=' : '!=';
				$criteria->where('calendarId', $op, null)
					->andWhere('ownerId', $op, null);
			});
	}

}