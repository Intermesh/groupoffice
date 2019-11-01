<?php
namespace go\modules\community\notes\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Query;

class NoteBook extends AclOwnerEntity {
	
	public $id;
	public $createdBy;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("notes_note_book");
	}

	protected static function internalDelete(Query $query) {

		if(!Note::delete(['noteBookId' => $query])) {
			return false;
		}

		return parent::internalDelete($query);
	}
}
