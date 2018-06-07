<?php
namespace go\modules\community\files\model;

use GO;
use go\core\acl\model;
use go\core\auth\model\User;
use go\core\db\Query;
use go\core\fs\Blob;
use go\core\fs\MetaData;
use go\core\orm\SearchableTrait;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;

class Node extends model\AclEntity {

	//use \go\core\orm\CustomFieldsTrait;
	use SearchableTrait;
	
	const InvalidNameRegex = "/[\\~#%&*{}\/:<>?|\"]/";
	const TempFilePatterns = [
		'/^\._(.*)$/',     // OS/X resource forks
		'/^.DS_Store$/',   // OS/X custom folder settings
		'/^desktop.ini$/', // Windows custom folder settings
		'/^Thumbs.db$/',   // Windows thumbnail cache
		'/^.(.*).swp$/',   // ViM temporary files
		'/^\.dat(.*)$/',   // Smultron seems to create these
		'/^~lock.(.*)#$/', // Windows 7 lockfiles
   ];
	
	public $name;
	protected $blobId;
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
	
	protected $parentAclId;
	
	protected static function defineMapping() {		
		return parent::defineMapping()
			->addTable('files_node', 'node')
			->addRelation('metaData', MetaData::class, ['blobId'=>'blobId'], false)
		   ->setQuery((new Query)
				->join('core_blob', 'blob', 'node.blobId=blob.id', 'LEFT')
				->join('files_node_user', 'nodeUser', 'node.id=nodeUser.nodeId AND nodeUser.userId='.GO()->getUserId().'', 'LEFT')
				->join('files_node', 'parent','node.parentId=parent.id','LEFT')
				->select('blob.contentType, blob.size, nodeUser.bookmarked, nodeUser.touchedAt, parent.aclId AS parentAclId'));
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
	
	public function setBlobId($blobId) {
		if($this->isDirectory) {
			return;
		}
		$this->blobId = $blobId;
		$blob = Blob::findById($blobId);
		$this->contentType = $blob->contentType;
		$this->size = $blob->size;
		$this->metaData = $blob->metaData;
	}
	
	public function getBlobId() {
		return $this->blobId;
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
		GO()->getDbConnection()->replace('files_node_user', ['bookmarked' => $this->bookmarked, 'userId' => GO()->getUserId(), 'nodeId' => $this->id])->execute();
	}
	
	/**
	 * Getter for bookmarked property (Needed because we needed to have a setter function
	 * 
	 * @return boolean
	 */
	public function getBookmarked(){
		return $this->bookmarked;
	}
	
	/**
	 * Set this folder as internalShared
	 * 
	 * @param boolean $val
	 */
	public function setInternalShared($val){
		
	}
	
	/**
	 * Getter for internalShared property (Needed because we needed to have a setter function
	 * 
	 * @return boolean
	 */
	public function getInternalShared(){
		return $this->aclId != $this->parentAclId;
	}
	
	protected function internalValidate() {
		$this->name = preg_replace(self::InvalidNameRegex, "_", $this->name);
		foreach (self::TempFilePatterns as $tempFile) {
			if (preg_match($tempFile, $this->name)) {
				$this->setValidationError('name', \go\core\validate\ErrorCode::MALFORMED, 'This is a temporary file');
			}
		}
		return parent::internalValidate();
	}
	
	/**
	 * Getter for externalShared property 
	 * 
	 * @return boolean
	 */
	public function getExternalShared(){
		return !empty($this->token);
	}
	
	public function getContentType() {
		return $this->contentType;
	}
	
	public function getSize() {
		return $this->size;
	}
	
	public function getParentId() {
		return $this->parentId;
	}
	
	public function setParentId($val) {
		$this->parentId = $val;
		$parent = self::find()->where(['id'=>$val])->single();
		if(!$parent){
			$this->setValidationError('parentId', ErrorCode::INVALID_INPUT, 'Parent not found');
			return;
		}
		if(!$parent->isDirectory) {
			$this->setValidationError('parentId', ErrorCode::INVALID_INPUT, 'Parent is not a directory');
			return;
		}
		if($parent->id == $this->id){
			$this->setValidationError('parentId', ErrorCode::INVALID_INPUT, 'Parent cannot be self');
			return;
		}
		$this->storageId = $parent->storageId;
		$this->aclId = $parent->aclId;
	}

	protected function getSearchDescription() {
		$user = User::findById(GO()->getUserId());
		
		return $user?$this->createdAt->format($user->date_format):'';
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
		$storage = Storage::find()->where(['ownedBy'=>GO()->getUserId()])->single();
		$userStorageId = $storage?$storage->id:null;

		if(isset($filter['q'])){
			$query->andWhere('name','LIKE', '%' . $filter['q'] . '%');
		}
		
		if(!empty($filter['bookmarked'])){
			$query->andWhere('nodeUser.bookmarked','=','1');
		}
		
		if(!empty($filter['isSharedWithMe'])){
			$query->andWhere('storageId','!=',$userStorageId);
		}
		
		$filterableProperties = ['parentId', 'isDirectory'];
		foreach($filterableProperties as $prop) {
			if(in_array($prop,array_keys($filter))){
				$query->andWhere([$prop => $filter[$prop]]);
			}
		}
		return parent::filter($query, $filter);		
	}
	
	public static function sort(Query $query, array $sort) {
		
		if(isset($sort['size'])) {			
			$query->orderBy(['blob.size' => $sort['size']]);			
		} 
		
		return parent::sort($query, $sort);
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
