<?php

namespace go\modules\community\comments\model;

use go\core\orm\Filter;

class CommentFilter extends Filter {

	public function setEntityId($value) {		
		$this->query->where('t.entityId', '=', $value);
	}

	public function setEntity($value) {		
		$this->query->where(['e.name' => $value]);		
	}
	
}
