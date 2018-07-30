<?php

namespace go\core\orm;

use DateTime;
use Exception;
use GO;
use go\core\App;
use go\core\db\Query;
use go\core\jmap\Entity;
use go\modules\core\modules\model\Module;

/**
 * The EntityType class
 * 
 * This holds information about the entity.
 * 
 * id: The ID in the database used for foreign keys
 * className: The PHP class name used in the PHP API
 * name: The name of the entity for the JMAP client API
 * moduleId: The module ID this entity belongs to
 * 
 * It's also used for routing short routes like "Note/get" instead of "community/notes/Note/get"
 * 
 */
class EntityType {

	private $className;	
	private $id;
	private $name;
	private $moduleId;	
  private $clientName;
	
	public $highestModSeq;
	
	/**
	 * The name of the entity for the JMAP client API
	 * 
	 * eg. "note"
	 * @return string
	 */
	public function getName() {
		return $this->clientName;
	}
	
	/**
	 * The PHP class name used in the PHP API
	 * 
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}
	
	/**
	 * The ID in the database used for foreign keys
	 * 
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * The module ID this entity belongs to
	 * 
	 * @return in
	 */
	public function getModuleId() {
		return $this->moduleId;
	}	
	
	
	/**
	 * Get the module this type belongs to.
	 * 
	 * @return Module
	 */
	public function getModule() {
		return Module::findById($this->moduleId);
	}

	/**
	 * Find by PHP API class name
	 * 
	 * @param string $className
	 * @return static
	 */
	public static function findByClassName($className) {

		$e = new static;
		$e->className = $className;
		
		$record = (new Query)
						->select('*')
						->from('core_entity')
						->where('clientName', '=', $className::getClientName())
						->single();

		if (!$record) {
			$module = Module::findByClass($className);
		
			if(!$module) {
				throw new Exception("No module found for ". $className);
			}

			$record = [];
			$record['moduleId'] = isset($module) ? $module->id : null;
			$record['name'] = self::classNameToShortName($className);
      $record['clientName'] = $className::getClientName();
			App::get()->getDbConnection()->insert('core_entity', $record)->execute();

			$record['id'] = App::get()->getDbConnection()->getPDO()->lastInsertId();
		} else
		{
			$e->highestModSeq = isset($record['highestModSeq']) ? (int) $record['highestModSeq'] : null;
		}

		$e->id = $record['id'];
		$e->moduleId = $record['moduleId'];
		$e->clientName = $record['clientName'];
		$e->name = $record['name'];
		
		
		return $e;
	}
	
	/**
	 * Creates a short name based on the class name.
	 * 
	 * This is used to generate response name. 
	 * 
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 * 
	 * @return string
	 */
	private static function classNameToShortName($cls) {
		return substr($cls, strrpos($cls, '\\') + 1);
	}
	
	/**
	 * Find all registered.
	 * 
	 * @return static[]
	 */
	public static function findAll() {
		$records = (new Query)
						->select('e.*, m.name AS moduleName, m.package AS modulePackage')
						->from('core_entity', 'e')
						->join('core_module', 'm', 'm.id = e.moduleId')						
						->all();
		
		$i = [];
		foreach($records as $record) {
			$i[] = static::fromRecord($record);
		}
		
		return $i;
	}

	/**
	 * Find by db id
	 * 
	 * @param int $id
	 * @return static
	 */
	public static function findById($id) {
		$record = (new Query)
						->select('e.*, m.name AS moduleName, m.package AS modulePackage')
						->from('core_entity', 'e')
						->join('core_module', 'm', 'm.id = e.moduleId')
						->where('id', '=', $id)
						->single();
		
		if(!$record) {
			return false;
		}
		
		return static::fromRecord($record);
	}
	
	/**
	 * Find by client API name
	 * 
	 * @param string $name
	 * @return static
	 */
	public static function findByName($name) {
		$record = (new Query)
						->select('e.*, m.name AS moduleName, m.package AS modulePackage')
						->from('core_entity', 'e')
						->join('core_module', 'm', 'm.id = e.moduleId')
						->where('clientName', '=', $name)
						->single();
		
		if(!$record) {
			return false;
		}
		
		return static::fromRecord($record);
	}
  

	private static function fromRecord($record) {
		$e = new static;
		$e->id = $record['id'];
		$e->name = $record['name'];
    $e->clientName = $record['clientName'];
		$e->moduleId = $record['moduleId'];
		$e->highestModSeq = (int) $record['highestModSeq'];

		if (isset($record['modulePackage'])) {
			$e->className = 'go\\modules\\' . $record['modulePackage'] . '\\' . $record['moduleName'] . '\\model\\' . ucfirst($e->name);
		} else {			
			$e->className = 'GO\\' . ucfirst($record['moduleName']) . '\\Model\\' . ucfirst($e->name);			
		}
		
		return $e;
	}
	
	protected $changed = [];
	
	public function change(Entity $entity) {
		$this->changed[] = $entity;		
	}
	
	/**
	 * Get the next state
	 * @param string $entityClass
	 * @return int
	 */
	private function nextModSeq() {
		/*
		 * START TRANSACTION
		 * SELECT counter_field FROM child_codes FOR UPDATE;
		  UPDATE child_codes SET counter_field = counter_field + 1;
		 * COMMIT
		 */


		$query = (new Query())
						->selectSingleValue("highestModSeq")
						->from("core_entity")
						->where(["id" => $this->id])
						->forUpdate();
		$modSeq = (int) $query->execute()->fetch();
		$modSeq++;

		App::get()->getDbConnection()
						->update(
										"core_entity", 
										['highestModSeq' => $modSeq],
										["id" => $this->id]
						)->execute(); //mod seq is a global integer that is incremented on any entity update
	
		return $modSeq;
	}	
	
	public function __destruct() {	
		
		
		if(!empty($this->changed)) {
			
			$this->highestModSeq = $this->nextModSeq();
			
			foreach($this->changed as $change) {
				$record = [
						'modSeq' => $this->highestModSeq,
						'entityTypeId' => $this->id,
						'entityId' => $change->id,
						'aclId' => $change->findAclId(),
						'destroyed' => $change->isDeleted(),
						'createdAt' => new DateTime()
								];
				

				GO()->getDbConnection()->replace('core_change', $record)->execute();
			}
			
		}
	}
	
	

}
