<?php
namespace go\core\acl\model;

use go\core\acl\model\Acl;
use go\core\acl\model\AclGroup;
use go\core\App;
use go\core\orm\Query;
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
abstract class AclOwnerEntity extends AclEntity {
	
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
		
		// Copy the default one.
		$defaultAcl = Acl::findById(static::getType()->getDefaultAclId());		
		$this->acl = $defaultAcl->copy();
		
		$this->acl->usedIn = $this->getMapping()->getColumn('aclId')->table->getName().'.aclId';
		$this->acl->ownedBy = !empty($this->createdBy) ? $this->createdBy : $this->getDefaultCreatedBy();
		
		if(!$this->acl->save()) {	
			throw new \Exception("Could not create ACL");
		}

		$this->aclId = $this->acl->id;		
	}
	
	protected function internalDelete() {
		if(!parent::internalDelete()) {
			return false;
		}
		
		if(!method_exists($this, 'aclEntityClass')) {
			$this->deleteAcl();
		}
		
		return true;
	}
	
	protected function deleteAcl() {		
		$acl = Acl::find()->where(['id' => $this->aclId])->single();
		if(!$acl->delete()) {
			throw new \Exception("Could not delete ACL ".$this->aclId);
		}
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
		return Acl::getUserPermissionLevel($this->aclId, App::get()->getAuthState()->getUserId());
	}
	
	/**
	 * Applies conditions to the query so that only entities with the given 
	 * permission level are fetched.
	 * 
	 * Note: when you join another table with an acl ID you can use Acl::applyToQuery():
	 * 
	 * ```
	 * $query = User::find();
	 * 
	 * $query	->join('applications_application', 'a', 'a.createdBy = u.id')
							->groupBy(['u.id']);
			
	 * //We don't want to use the Users acl but the applications acl.
			\go\core\acl\model\Acl::applyToQuery($query, 'a.aclId');
	 * 
	 * ```
	 * 
	 * @param Query $query
	 * @param int $level
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null) {			
		$tables = static::getMapping()->getTables();
		$firstTable = array_shift($tables);
		$tableAlias = $firstTable->getAlias();
		Acl::applyToQuery($query, $tableAlias . '.aclId', $level, $userId);
		
		return $query;
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

	/**
	 * Get the table alias holding the aclId
	 */
	public static function getAclEntityTableAlias() {
		return static::getMapping()->getColumn('aclId')->table->getAlias();
	}

}
