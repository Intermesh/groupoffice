<?php
namespace go\modules\community\notes\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\model\Acl;
use go\core\model\Module;
use go\core\orm\Mapping;
use go\core\orm\Query;

class NoteBook extends AclOwnerEntity {
	
	public $id;
	public $createdBy;
	public $name;
	public $filesFolderId;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("notes_note_book", "nb");
	}

	protected function canCreate(): bool
	{
		return Module::findByName('community', 'notes')
			->getUserRights()->mayChangeNoteBooks;
	}

	protected static function internalDelete(Query $query): bool
	{

		if(!Note::delete(['noteBookId' => $query])) {
			return false;
		}

		return parent::internalDelete($query);
	}

	protected static function textFilterColumns(): array
	{
		return ['name'];
	}
}
