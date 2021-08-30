<?php


namespace go\core\model;


use go\core\orm\Property;

/**
 * Class Permission
 * @property $ownerEntity \go\core\model\Module
 */
class Permission extends Property {

	protected $moduleId;
	protected $groupId;
	protected $rights;

	protected static function defineMapping() {
		return parent::defineMapping()->addTable("core_permission");
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