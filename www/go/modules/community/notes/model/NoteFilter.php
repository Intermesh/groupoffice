<?php
namespace go\modules\community\notes\model;

use go\core\db\Criteria;
use go\core\orm\Filter;

class NoteFilter extends Filter {
	public function setNoteBookId($noteBookId) {
		$this->query->andWhere(['noteBookId' => $noteBookId]);
	}
	
	public function setQ($value) {
		if(empty($value)) {
			return;
		}
		
		$this->query->andWhere(
						(new Criteria())
						->where('name','LIKE', '%' . $value . '%')
						->orWhere('content', 'LIKE', '%' . $value . '%')
						);
	}
}
