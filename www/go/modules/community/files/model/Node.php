<?php
namespace go\modules\community\files\model;

use go\core\acl\model;

class Node extends model\AclItemEntity {

	use go\core\orm\CustomFieldsTrait;
	use go\core\orm\SearchableTrait;
	
	public $name;
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
	public $isDirectory;
	
	public $comments;
	public $bookmarked;
	/**
	 * @var \go\core\util\DateTime
	 */
	public $touchedAt;
	public $storageId;
	public $parentId;
	
	protected static function defineMapping() {
		return parent::defineMapping();
	}
	
	public function getPath() {
		return $this->parent->getPath().'/'.$this->name;
	}

	protected static function aclEntityClass() {
		return Storage::class;
	}

	protected static function aclEntityKeys() {
		return ['storageId' => 'id'];
	}

	protected function getSearchDescription() {
		return $this->createdAt;
	}

	protected function getSearchName() {
		return $this->name;
	}

}