<?php

namespace go\core\orm;

use go\core\App;
use go\core\db\Query;
use go\core\Singleton;

class StateManager extends Singleton {

	/**
	 * Get the current state of this entity
	 * 
	 * @return int
	 */
	public function current($entityClass = null) {
		
		if(!isset($entityClass)) {
			$entityClass = Entity::class;
		}
		
		return (int) (new Query())
										->selectSingleValue("highestModSeq")
										->from("core_state")
										->where(["entityClass" => $entityClass])
										->execute()
										->fetch();
	}

	/**
	 * Get the next state
	 * @param string $entityClass
	 * @return int
	 */
	public function next($entityClass = null) {
		/*
		 * START TRANSACTION
		 * SELECT counter_field FROM child_codes FOR UPDATE;
		  UPDATE child_codes SET counter_field = counter_field + 1;
		 * COMMIT
		 */


		$query = (new Query())
						->selectSingleValue("highestModSeq")
						->from("core_state")
						->where(["entityClass" => Entity::class])
						->forUpdate();
		$state = (int) $query->execute()->fetch();
		$state++;

		App::get()->getDbConnection()
						->replace(
										"core_state", ["entityClass" => Entity::class, 'highestModSeq' => $state]
						)->execute(); //mod seq is a global integer that is incremented on any entity update

		if (isset($entityClass)) {
			App::get()->getDbConnection()
							->replace(
											"core_state", ["entityClass" => $entityClass, 'highestModSeq' => $state]
							)->execute();
		}
		return $state;
	}

}
