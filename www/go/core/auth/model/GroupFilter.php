<?php
namespace go\core\auth\model;

use go\core\db\Criteria;
use go\core\orm\Filter;

class GroupFilter extends Filter {
	public function setIncludeUsers($include = true) {
		if(!$include) {
			$this->query->andWhere(['isUserGroupFor' => null]);
		}
	}
	
	public function setQ($value) {
		if(empty($value)) {
			return;
		}
		
		$this->query->andWhere(
						(new Criteria())
						->where('name','LIKE', '%' . $value . '%')
						);
	}
}

