<?php
namespace go\core\acl\model;

use go\core\model\Acl;
use go\core\orm\exception\SaveException;
use go\core\util\ArrayObject;

trait AclSetterTrait {


	/**
	 * The acl entity
	 * @var Acl
	 */
	private $acl;

	/**
	 * Get the ACL entity
	 *
	 * @return Acl
	 * @throws Exception
	 */
	public function findAcl(): ?Acl
	{
		if(empty($this->{static::$aclColumnName})) {
			return null;
		}
		if(!isset($this->acl)) {
			$this->acl = Acl::internalFind()->where(['id' => $this->{static::$aclColumnName}])->single();
		}

		return $this->acl;
	}

	/**
	 *
	 * @throws Exception
	 */
	protected function saveAcl()
	{
		if(!isset($this->setAcl)) {
			return;
		}

		$a = $this->findAcl();

		if(!$a) {
			throw new \Exception("There's no ACL set for this entity");
		}

		foreach($this->setAcl as $groupId => $level) {
			$a->addGroup($groupId, $level);
		}

		if(!$a->save()) {
			throw new SaveException($a);
		}
	}

	/**
	 * Returns an array with group ID as key and permission level as value.
	 *
	 * @return array eg. ["2" => 50, "3" => 10]
	 * @throws Exception
	 */
	public function getAcl(): ArrayObject
	{
		$a = $this->findAcl();

		$acl = new ArrayObject();
		$acl->serializeJsonAsObject = true;

		if(empty($a->groups)) {
			//return null because an empty array is serialzed as [] instead of {}
			return $acl;
		}

		if($a) {
			foreach($a->groups as $group) {
				$acl[$group->groupId] = $group->level;
			}
		}

		return $acl;
	}

	protected $setAcl;

	/**
	 * Set the ACL
	 *
	 * @param array|null $acl An array with group ID as key and permission level as value. eg. ["2" => 50, "3" => 10]
	 *
	 * @example
	 * ```
	 * $addressBook->setAcl([
	 *  Group::ID_INTERNAL => Acl::LEVEL_DELETE
	 * ]);
	 * ```
	 */
	public function setAcl(?array $acl)
	{
		$this->setAcl = $acl;
	}

	/**
	 * Check if the ACL was modified
	 *
	 * @return bool
	 */
	public function isAclModified() : bool{
		return isset($this->setAcl);
	}
}