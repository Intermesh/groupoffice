<?php

namespace go\core\links;

use go\core\orm\Filter;

class LinkFilter extends Filter {

	public function setEntityId($value) {		
		$this->query->where('fromId', '=', $value);
	}

	public function setEntity($value) {		
		$this->query->where(['eFrom.name' => $value]);		
	}
	
}
