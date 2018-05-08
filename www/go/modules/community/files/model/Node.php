<?php
namespace go\modules\community\files\model;

use GO;
use go\core\acl\model;
use go\core\db\Query;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;

class Node extends model\AclEntity {

	//use \go\core\orm\CustomFieldsTrait;
	use SearchableTrait;
	
	public $name;
	public $blobId;
	/**
	 * @var DateTime
	 */
	public $createdAt;
	/**
	 * @var DateTime
	 */
	public $modifiedAt;
	public $ownedBy;
	public $modifiedBy;
	public $modified; // needed because core_blob has this column is column en there is a getModfied() function
	public $isDirectory;
	protected $size;
	protected $contentType;
	
	public $comments;
	public $bookmarked;
	/**
	 * @var DateTime
	 */
	public $touchedAt;
	public $storageId;
	protected $parentId;
	
	public $aclId;
	
	protected static function defineMapping() {		
		return parent::defineMapping()
			->addTable('files_node', 'node')
		   ->setQuery((new Query)
							 ->join('core_blob', 'blob', 'node.blobId=blob.id', 'LEFT')
							 ->join('files_node_user', 'nodeUser', 'node.id=nodeUser.nodeId AND nodeUser.userId='.GO()->getUser()->id.'', 'LEFT')
							 ->select('blob.contentType, blob.size, nodeUser.bookmarked, nodeUser.touchedAt'));
//			->addTable('core_blob', 'blob', ['blobId' => 'id'], ['contentType','size']);
	}
	
	/**
	 * @todo entity should be smart on the joins
	 */
	protected function init() {
		parent::init();
		
		
		if(isset($this->touchedAt)) {
			$this->touchedAt = new DateTime($this->touchedAt);
		}
		
		$this->bookmarked = boolval($this->bookmarked);
	}
	
	/**
	 * Set bookmarked for the current user
	 * 
	 * @TODO This function should not be needed when the join in "defineMapping" is changed in a "addTable" property.
	 * 
	 * @param bool $val
	 */
	public function setBookmarked($val) {
		$this->bookmarked = $val;
		GO()->getDbConnection()->replace('files_node_user', ['bookmarked' => $this->bookmarked], ['userId' => GO()->getUser()->id, 'nodeId' => $this->nodeId])->execute();
	}
	
	public function getContentType() {
		return $this->contentType;
	}
	
	public function getSize() {
		return $this->size;
	}
	
	public function getLocation() {
		return '/biem/'.$this->name;
	}
	
	public function getParentId(){
		return $this->parentId;
	}
	public function setParentId($val) {
		$this->parentId = $val;
		$this->storageId = self::find()->selectSingleValue('storageId')->where(['id'=>$val])->single();
	}

	protected function getSearchDescription() {
		return $this->createdAt->format(GO()->getUser()->date_format);
	}

	protected function getSearchName() {
		return $this->name;
	}
	
	/**
	 * Tells if this folder has children.
	 * 
	 * @return boolean
	 */
	public function getHasChildren(){
		$hasChild = self::find()->where(['parentId'=>$this->id,'isDirectory'=>true])->single();
		return $hasChild?true:false;
	}
	
	public static function filter(Query $query, array $filter) {
		
		// Add where usergroup is the personal group of the user
		if(isset($filter['isHome'])){
			$homeDirId = GO()->getUser()->storage->getRootFolderId();
						
			if(!empty($filter['isHome'])){
				// We are querying the "home dir" of the current user
				$query->andWhere(['parentId' => $homeDirId]);
			} else {
				// We are querying the "shared with me" dir of the current user
				$query->andWhere('parentId','!=',$homeDirId);
				$query->andWhere('id','!=',0);
				$query->andWhere('storageId','!=',GO()->getUser()->storage->id);
			}
		}
		
		if(isset($filter['q'])){
			$query->andWhere('name','LIKE', '%' . $filter['q'] . '%');
		}
		
		if(!empty($filter['bookmarked'])){
			$query->andWhere('nodeUser.bookmarked','=','1');
		}
		
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
			['metaData', 'contentType', 'size', 'blobId', 'versions'] :
			['items', 'subscribed', 'canAddItems'];
		foreach($unset as $key) { 
			unset($result[$key]); 
		}
		return $result;
	}

}
