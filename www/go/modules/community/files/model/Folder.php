<?php
namespace go\modules\community\files\model;

class Folder extends Node {
	
	public $items;
	public $subscribed;
	public $canAddItems;
	
	protected static function defineMapping() {
		return parent::defineMapping()
					->addTable("files_folder", "folder");
	}
	
	public function getHasChildren(){
		
		// Select where parent id = this is.
		//return found?true:false;
		
	}
	
}