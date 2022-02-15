<?php
/**
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\tasks\model;
						
use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\model\Module;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\validate\ErrorCode;

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
	public $tasklistId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("tasks_category", "category");
	}

	protected function init()
	{
		parent::init();

		if($this->isNew())  {
			$this->ownerId = go()->getUserId();
		}
	}

	public function getPermissionLevel(): int
	{
		//global category mayb only be created by admins
		if(empty($this->tasklistId)) {
			return Module::findByName('community', 'tasks')
				->getUserRights()
				->mayChangeCategories ? Acl::LEVEL_MANAGE : 0;
		}

		$tasklist = Tasklist::findById($this->tasklistId);

		return $tasklist->getPermissionLevel() >= Acl::LEVEL_MANAGE ? Acl::LEVEL_DELETE : Acl::LEVEL_READ;
	}


	public static function getClientName(): string
	{
		return "TaskCategory";
	}

	protected static function textFilterColumns(): array
	{
		return ['name'];
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('ownerId', function(Criteria $criteria, $value) {
				$criteria->where('ownerId', '=', $value)->orWhere('ownerId', 'IS', null);
			})
			->add('tasklistId', function(Criteria $criteria, $value) {
				if($value !== null) {
					$criteria->where('tasklistId', '=', $value);
				}
				$criteria->orWhere('tasklistId', '=', null);
			}, null);
	}

}