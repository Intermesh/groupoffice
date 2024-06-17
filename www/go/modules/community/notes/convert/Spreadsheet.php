<?php


namespace go\modules\community\notes\convert;

use go\core\data\convert;
use go\modules\community\notes\model\Note;
use go\modules\community\notes\model\NoteBook;
use go\modules\community\tasks\model\TaskList;


class Spreadsheet extends convert\Spreadsheet
{
	public static $excludeHeaders = [ 'password', 'images'];

	protected function init() {
		$this->addColumn('notebook', go()->t('Notebook', 'community', 'notes'));
	}
	protected function exportList(Note $note) {
		$nb = NoteBook::findById($note->noteBookId, ['name']);
		return $nb->name;
	}

	protected function importList(Note $note, $value, array $values) {
		$nb = TaskList::find(['id'])->where('name', '=', $value);
		if($nb) {
			$note->noteBookId = $nb->id;
		}
	}

}