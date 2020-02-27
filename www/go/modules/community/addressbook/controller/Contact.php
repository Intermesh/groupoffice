<?php
namespace go\modules\community\addressbook\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\modules\community\addressbook\model;

/**
 * The controller for the Contact entity
 *
 * @copyright (c) 2018, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */ 
class Contact extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass() {
		return model\Contact::class;
	}	
	
	
	
	protected function transformSort($sort) {
		$sort = parent::transformSort($sort);
		
		//merge sort on start to beginning of array
		return array_merge(['s.starred' => 'DESC'], $sort);
	}
	
	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {

		if(isset($params["destroy"])) {
			foreach($params['destroy'] as $id) {

				$contactMailTime = \go\modules\community\addressbook\model\ContactMailTime::model()->findByPk(array(
					'contact_id'=>$id,
					'user_id'=> \GO::user()->id
				));

				if($contactMailTime) {
					$contactMailTime->delete();
					$contactMailTime->save();
				}
			}
		}

		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}
	
	public function export($params) {
		return $this->defaultExport($params);
	}
	
	public function import($params) {
		return $this->defaultImport($params);
	}
	
	public function importCSVMapping($params) {
		return $this->defaultImportCSVMapping($params);
	}


	public function merge($params) {
		return $this->defaultMerge($params);
	}
}

