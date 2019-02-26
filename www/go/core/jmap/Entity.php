<?php

namespace go\core\jmap;

use go\core\orm\StateManager;
use go\core\util\DateTime;

/**
 * Entity model
 * 
 * An entity is a model that is saved to the database. An entity can have 
 * multiple database tables. It can be extended with has one related tables and
 * it can also have properties in other tables.
 */
abstract class Entity  extends \go\core\orm\Entity {	
	
	/**
	 * Track changes in the core_change log for the JMAP protocol.
	 * Disabled during install.
	 * 
	 * @var boolean 
	 */
	public static $trackChanges = true;
	
	/**
	 * The Entity ID
	 * 
	 * @var int
	 */
	public $id;

	/**
	 * Get the current state of this entity
	 * 
	 * @return int
	 */
	public static function getState() {
		return static::getType()->highestModSeq;
	}

	/**
	 * Saves the model and property relations to the database
	 * 
	 * Important: When you override this make sure you call this parent function first so
	 * that validation takes place!
	 * 
	 * @return boolean
	 */
	protected function internalSave() {
		
		if(!parent::internalSave()) {
			return false;
		}
		
		if(self::$trackChanges) {
			$this->getType()->change($this);		
		}
		
		return true;
	}
	
	/**
	 * Implements soft delete
	 * 
	 * @return boolean
	 */
	protected function internalDelete() {
		
		if(!parent::internalDelete()) {
			return false;
		}
		
		if(self::$trackChanges) {
			$this->getType()->change($this);
		}
		
		return true;
	}	
}
