<?php


namespace go\modules\community\calendar\model;

use go\core\model\Acl;
use go\core\model\User;
use go\core\orm\Mapping;
use go\core\jmap\Entity;
use go\core\model\Module;
use go\core\orm\Query;

class ResourceGroup extends Entity
{
	// Omit for the default alerts (with or without time)
	public ?string $id;
	/** @var string The user-visible name of the calendar */
	public string $name;
	public ?string $description;
	/* @var bool If true, all resources in this resource group will be automatically accepted if free. */
	public ?bool $autoAccept;

	/**
	 *
	 * This user will be used for 3 things:
	 *
	 * 1. This user may manage the group
	 * 2. The principal built from the resource calendar will get the e-mail of this user.
	 * 3. This user will be granted manage permissions when new resource calendars are created
	 *
	 * @todo rename because the name is misleading. It's not a default for the calendar->ownerId. This will be null.
	 * Calendar::getOwner() will return Calendar::$id as principal/owner. Also the principals will be out of date when it changes.
	 * @var ?string
	 */
	public ?string $defaultOwnerId;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('calendar_resource_group', "rg");
	}

	protected function internalGetPermissionLevel(): int
	{
		if(go()->getUserId() == $this->defaultOwnerId && Module::findByName('community', 'calendar')
			->getUserRights()->mayChangeResources) {
			return Acl::LEVEL_MANAGE;
		}
		return parent::internalGetPermissionLevel();
	}

	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, ?int $userId = null, ?array $groups = null): Query
	{
		if($level > Acl::LEVEL_READ && !User::isAdminById($userId ?? go()->getUserId())) {
			$query->where('defaultOwnerId', '=', $userId ?? go()->getUserId());
		}
		return $query;
	}

}