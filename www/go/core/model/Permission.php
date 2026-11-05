<?php


namespace go\core\model;


use go\core\orm\Mapping;
use go\core\orm\Property;

/**
 * Class Permission
 * @property $ownerEntity \go\core\model\Module
 */
class Permission extends Property {

	public $moduleId;
	public $groupId;
	protected $rights;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("core_permission");
	}

	/**
	 * Get the rights bitmask a user has for a module, combined over all groups of the user.
	 *
	 * A permission record grants read access, its bits grant the extra module rights.
	 *
	 * @param int $moduleId
	 * @param int $userId
	 * @return int|null null if none of the user's groups has a permission record for the module, 0 for read access only
	 */
	public static function findUserRights(int $moduleId, int $userId): ?int
	{
		// BIT_OR() returns 0 on an empty set. Group so that no permission record returns no row and single() returns null.
		$rights = go()->getDbConnection()->selectSingleValue("BIT_OR(rights)")
			->from("core_permission")
			->where('moduleId', '=', $moduleId)
			->where("groupId", "IN",
				go()->getDbConnection()
					->select("groupId")
					->from("core_user_group")
					->where(['userId' => $userId])
			)
			->groupBy(['moduleId'])
			->single();

		return $rights === null ? null : (int) $rights;
	}

	public function hasRight($name){
		$types = $this->owner->module()->getRights();
		return !!($this->rights & $types[$name]);
	}

	// int to [name => bool]
	public function getRights(){
		$types = $this->owner->module()->getRights();
		$rights = ['mayRead' => true];
		foreach($types as $name => $bit){
			if($this->rights & $bit) {
				$rights[$name] = true;
			}
		}
		return (object)$rights;
	}

	// [name => bool] to int
	public function setRights($rights){
		$types = $this->owner->module()->getRights();
		$this->rights = 0; // need to post all active rights this way
		foreach($rights as $name => $isTrue){
			if(!isset($types[$name])) continue; // do not set invalid permissions
			if($isTrue) {
				$this->rights |= $types[$name]; // add
			//} else {
			//	$this->rights ^= $types[$name]; // remove
			}
		}

		return $this;
	}
}