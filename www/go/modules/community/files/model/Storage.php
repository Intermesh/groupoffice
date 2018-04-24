<?php
namespace go\modules\community\files\model;

use go\core\orm;

class Storage extends orm\Entity {

	
	public $id;
	/**
	 * @var \go\core\util\DateTime
	 */
	public $createdAt;
	/**
	 * @var \go\core\util\DateTime
	 */
	public $modifiedAt;
	public $ownedBy;
	public $modifiedBy;
	
	public $quota;
	public $usage;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable("files_storage");
	}

}