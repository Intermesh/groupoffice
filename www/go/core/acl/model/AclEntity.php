<?php
namespace go\core\acl\model;

use go\core\acl\model\Acl;
use go\core\acl\model\AclGroup;
use go\core\App;
use go\core\db\Query;
use go\core\jmap\Entity;
use go\core\orm\Mapping;

/**
 * The AclEntity
 * 
 * Is an entity that has an "aclId" property. The ACL is used to restrict access
 * to the entity.
 * 
 * @see Acl
 */
abstract class AclEntity extends Entity {
	
	/**
	 * The ID of the {@see Acl}
	 * 
	 * @var int
	 */
	public $aclId;
	
	/**
	 * The acl entity
	 * @var Acl 
	 */
	private $acl;
	
//	Disabled for performance reasons. How should we handle this?
//	/**
//	 * The groups in the ACL with their level
//	 * 
//	 * @var AclGroup[]
//	 */
//	public $acl = [];
//	
//	protected static function defineMapping() {
//		return parent::defineMapping()
//						->addRelation('acl', AclGroup::class, ['aclId' => 'aclId'], true);
//	}
	
	protected function internalSave() {
		
		if($this->isNew() && !isset($this->aclId)) {
			$this->createAcl();
		}
		
		return parent::internalSave();
	}
	
	protected function createAcl() {
		$this->acl = new Acl();
		$this->acl->usedIn = $this->getMapping()->getColumn('aclId')->table->getName().'.aclId';
		$this->acl->ownedBy = $this->getCreatedBy();

		if(!$this->acl->internalSave()) {	
			return false;
		}

		$this->aclId = $this->acl->id;
	}
	
	/**
	 * Get the ACL entity
	 * 
	 * @return Acl
	 */
	public function findAcl() {
		if(empty($this->aclId)) {
			return null;
		}
		if(!isset($this->acl)) {
			$this->acl = Acl::internalFind()->where(['id' => $this->aclId])->single();
		}
		
		return $this->acl;
	}
	
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	public function getPermissionLevel() {
		return Acl::getPermissionLevel($this->aclId, App::get()->getAuthState()->getUserId());
	}
	
	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ) {			
		$tables = static::getMapping()->getTables();
		$firstTable = array_shift($tables);
		$tableAlias = $firstTable->getAlias();
		Acl::applyToQuery($query, $tableAlias . '.aclId', $level);
		
		return $query;
	}
	
	public static function filter(Query $query, array $filter) {
		if(!empty($filter['permissionLevel'])) {
			static::applyAclToQuery($query, $filter['permissionLevel']);
		}
		return parent::filter($query, $filter);
	}
	
	/**
	 * Finds all aclId's for this entity
	 * 
	 * This query is used in the "getFooUpdates" methods of entities to determine if any of the ACL's has been changed.
	 * If so then the server will respond that it cannot calculate the updates.
	 * 
	 * @see \go\core\jmap\EntityController::getUpdates()
	 * 
	 * @return Query
	 */
	public static function findAcls() {
		$tables = static::getMapping()->getTables();
		$firstTable = array_shift($tables);
		return (new Query)->selectSingleValue('aclId')->from($firstTable->getName());
	}
	
	public function findAclId() {
		return $this->aclId;
	}

}
