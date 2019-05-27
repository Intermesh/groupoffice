<?php
namespace go\modules\community\notes\model;

use go\core\acl\model\AclOwnerEntity;

class NoteBook extends AclOwnerEntity {
	
	public $id;
	public $createdBy;
	public $name;
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable("notes_note_book");
	}

	protected function internalDelete() {
		if(!Note::find()->where(['noteBookId' => $this->id])->delete()) {
			return false;
		}

		return parent::internalDelete();
	}
}
