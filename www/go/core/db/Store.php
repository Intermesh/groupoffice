<?php
namespace go\core\db;

use go\core\data;


/**
 * Find operations return this collection object
 * 
 * It holds {@see Record} models.
 *
 * This is generally not used directly. {@see Record::find()}
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Store extends data\Store {
	
	public function next() {
		return $this->getIterator()->fetch();
	}
	
}
