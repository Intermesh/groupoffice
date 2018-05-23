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
	 * The Entity ID
	 * 
	 * @var int
	 */
	public $id;

	/**
	 * The modseq when the entity was last modified or deleted.
	 * 
	 * It's a global integer that is incremented on any entity update.
	 * 
	 * @var int  
	 */
	public $modSeq;

	/**
	 * When an entity is deleted it's not really deleted. Only deletedAt is set to the time when it was deleted.
	 * The {@see find()} method will add "where deletedAt is null" to the query conditions.
	 * @var DateTime
	 */
	public $deletedAt;

	/**
	 * Get the current state of this entity
	 * 
	 * @return int
	 */
	public static function getState() {
		return StateManager::get()->current(static::class);
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
		
		$this->modSeq = StateManager::get()->next(static::class);
		
		return parent::internalSave();
	}
	
	/**
	 * Implements soft delete
	 * 
	 * @return boolean
	 */
	protected function internalDelete() {
		$this->deletedAt = new DateTime();

		return $this->internalSave();
	}
	
	/**
	 * Hard delete the entity
	 * 
	 * Don't set "deletedAt" but purge it from the database.
	 * 
	 * @return boolean
	 */
	public function deleteHard() {
		return parent::internalDelete();
	}
	
	protected static function internalFind( array $fetchProperties = []) {
		
		$query = parent::internalFind($fetchProperties);
		
		//for compatibility with old models
		if(static::getMapping()->getColumn('deletedAt')) {
			$query->andWhere(['deletedAt' => NULL]);
		}
		
		return $query;
		
	}
	
	/**
	 * Get's the class name without the namespace
	 * 
	 * eg. class go\modules\community\notes\model\Note becomes just "note"
	 * 
	 * @return string
	 */
	public static function getClassName() {
		$cls = static::class;
		return substr($cls, strrpos($cls, '\\') + 1);
	}
}
