<?php

namespace go\core\search;

use go\core\orm\Filter;
/**
 * filter: {q: "string"}
 */
class SearchFilter extends Filter {

	public function setQ($value) {
		if (!empty($value)) {
			$this->query->where('keywords', 'LIKE', "%" . $value . "%");
		}
	}

	public function setEntities($value) {		
		$this->query->where('e.name', 'IN', $value);		
	}
	
}
