<?php
namespace go\modules\community\notes\model;

use go\core\acl\model\AclEntity;

class NoteBook extends AclEntity {
	
	public $id;
	public $createdBy;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("notes_note_book");
	}
	
}
