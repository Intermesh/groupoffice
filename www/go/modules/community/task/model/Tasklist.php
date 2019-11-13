<?php
namespace go\modules\community\task\model;
						
use go\core\acl\model\AclOwnerEntity;

/**
 * Tasklist model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Tasklist extends AclOwnerEntity {
	
	/**
	 * 
	 * @var int
	 */							
	public $id;

	/**
	 * 
	 * @var string
	 */							
	public $name;

	/**
	 * 
	 * @var int
	 */							
	public $createdBy;

	/**
	 * 
	 * @var int
	 */							
	public $aclId;

	/**
	 * 
	 * @var int
	 */							
	public $filesFolderId = 0;

	/**
	 * 
	 * @var int
	 */							
	public $version = 1;

	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("task_tasklist", "tasklist");
	}

	public static function getClientName() {
		return "Tasklist";
	}

}