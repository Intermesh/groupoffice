<?php

namespace go\modules\community\calendar\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\model\Module;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * Category model
 */
class Category extends Entity {

	public ?string $id;

	/** @var string */
	public string $name;

	/** @var ?int could be NULL for global categories */
	protected ?int $ownerId = null;

	public ?string $color;

	/** @var ?string When not null this category is only visible when the tasklist is selected (no ACL checking allowed)  */
	public ?string $calendarId = null;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("calendar_category", "category");
	}

	public function setOwnerId($id) {
		if(go()->getAuthState()->isAdmin())
			$this->ownerId = $id; // only admin may create global categories
	}

	public function getOwnerId() {
		return $this->ownerId;
	}

	protected function init()
	{
		parent::init();

		if($this->isNew() )  {
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

			return $calendar->getPermissionLevel() >= Acl::LEVEL_MANAGE ? Acl::LEVEL_MANAGE : Acl::LEVEL_READ;
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
			->add('inCalendars', function(Criteria $criteria, $value, Query $query) {
				if($value === 'visibleOnly') {
					$query->join('calendar_calendar_user', 'ucal', 'ucal.id = category.calendarId AND ucal.userId = '.go()->getAuthState()->getUserId(), 'LEFT');
						$criteria
						->where(['ucal.isVisible' => true])
						->orWhere('category.calendarId', 'IS', null);
				} else if(!empty($value)) {
					$criteria->andWhere(['category.calendarId' => $value]);
				}
			}, 'visibleOnly')
			->add('ownerId', function(Criteria $criteria, $value) {
				$criteria->where('ownerId', '=', $value)
					->andWhere('calendarId' , '=', null);
			})
			->add('calendarId', function(Criteria $criteria, $value) {
				$criteria->where('calendarId', '=', $value);
			})->add('name', function(Criteria $criteria, $value) {
				$criteria->where('name', 'LIKE', '%'.$value.'%');
			})
			->add('mine', function(Criteria $criteria, $value) {
				$criteria->where('calendarId', 'IS', null)
					->andWhere((new Criteria())
						->where('ownerId', 'IS', null)
						->orWhere('ownerId','=', go()->getUserId())
					);
			},'1');
	}

}