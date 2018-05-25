<?php
namespace go\modules\community\files\model;

use Exception;
use go\core\db\Query;
use go\core\jmap;
use go\core\util\DateTime;

class Storage extends jmap\Entity {

	public $id;

	/**
	 * @var DateTime
	 */
	public $modifiedAt;
	public $ownedBy;	
	public $quota;
	protected $usage;
	
	protected $structure;
	
	protected static function defineMapping() {
		return parent::defineMapping()->addTable("files_storage");
	}

	public static function filter(Query $query, array $filter) {
				
		$filterableProperties = ['ownedBy'];
		foreach($filterableProperties as $prop) {
			if(in_array($prop,array_keys($filter))){
				$query->andWhere([$prop => $filter[$prop]]);
			}
		}
		return parent::filter($query, $filter);		
	}
	
	public function getRootFolderId(){
		
		$id = Node::find()->selectSingleValue('node.id')->where(['storageId'=>$this->id,'parentId'=>0])->single();
		if($id === false) {
			throw new Exception('No root folder for storage: '.$this->id);
		}
		return (int) $id;
	}
	
	public function updateUsage(){
		$this->usage = Node::find()->selectSingleValue('SUM(blob.size)')->where(['node.storageId'=>$this->id])->single();
		$this->save();
	}
	
	public function getUsage(){
		return Node::find()->selectSingleValue('SUM(blob.size)')->where(['node.storageId'=>$this->id])->single();
	}
}
