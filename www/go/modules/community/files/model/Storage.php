<?php
namespace go\modules\community\files\model;

use go\core\acl\model;

class Node extends model\AclItemEntity {

	use go\core\orm\CustomFieldsTrait;
	use go\core\orm\SearchableTrait;
	
	public $rootFolderId;
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
		return parent::defineMapping()->addTable("files_storage", "s");
	}


	protected static function aclEntityClass() {
		return Storage::class;
	}



}