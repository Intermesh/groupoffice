<?php
namespace go\modules\community\files\model;

use go\core\acl\model;
use go\core\db\Query;

class Node extends model\AclEntity {

	//use \go\core\orm\CustomFieldsTrait;
	use \go\core\orm\SearchableTrait;
	
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
		return parent::defineMapping()->addTable('files_node', 'node');
	}
	
	public function getPath() {
		return '/biem/'.$this->name;
	}

	protected function getSearchDescription() {
		return $this->createdAt;
	}

	protected function getSearchName() {
		return $this->name;
	}
	
	public static function filter(Query $query, array $filter) {	
		$filterableProperties = ['parentId', 'isDirectory'];
		foreach($filterableProperties as $prop) {
			if(isset($filter[$prop])) {
				$query->andWhere([$prop => $filter[$prop]]);
			}
		}
		return parent::filter($query, $filter);		
	}
	
	public function toArray($properties = array()) {
		$result = parent::toArray($properties);
		$unset = ($result['isDirectory']) ?
			['metaData', 'mimeType', 'byteSize', 'bloId', 'versions'] :
			['items', 'subscribed', 'canAddItems'];
		foreach($unset as $key) { 
			unset($result[$key]); 
		}
		unset($result['isDirectory']);
		return $result;
	}

}
