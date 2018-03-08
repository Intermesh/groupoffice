<?php
namespace go\modules\community\test\model;

use go\core\orm\Filter;

class BFilter extends Filter {	
	
	public function setPropA($value) {
		$this->query->andWhere('propA', 'LIKE', $value . "%");
	}
	
	public function setPropB($value) {
		$this->query->andWhere('propB', 'LIKE', $value . "%");
	}
	
	public function setHasHasMany($value) {		
		$tables = AHasMany::getMapping()->getTables();
		$firstTable = array_shift($tables);
		
		$this->query->join($firstTable->getName(), 'hasMany', 'a.id = hasMany.aId')->groupBy(['a.id']);
			
		$this->query->andWhere('hasMany.propOfHasManyA', "LIKE", "%" . $value . "%");
	}
}