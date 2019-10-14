<?php
namespace go\modules\community\test\model;

use go\core\db\Query;


/**
 * Extends model A and demonstrates usage of a second table
 * 
 */
class B extends A {
	
	/**
	 *
	 * @var string
	 */
	public $propB;
	
	public $cId;
	
	/**
	 * The sum of all ID's in table B
	 * 
	 * @var int
	 */
	protected $sumOfTableBIds;
	
	
	protected static function defineMapping() {
		$mapping = parent::defineMapping()
			->addTable('test_b', 'b', ['id' => 'id'], null, ['userId' => GO()->getUserId()])
			->setQuery((new Query())->select("SUM(b.id) AS sumOfTableBIds")->join('test_b', 'bc', 'bc.id=a.id')->groupBy(['a.id']));
		
		return $mapping;
	}
	
		
	public function getSumOfTableBIds() {
		return $this->sumOfTableBIds;
	}
	
	/**
	 * 
	 * @return C
	 */
	public function getC() {
		return C::findById($this->cId);
	}
	
	public static function filter(Query $query, array $filter) {
		
		if(isset($filter['propA'])) {
			$query->andWhere('propA', 'LIKE', $filter['propA'] . "%");
		}

		if(isset($filter['propB'])) {
			$query->andWhere('propB', 'LIKE', $filter['propB'] . "%");
		}

		if(isset($filter['hasHasMany'])) {
			$tables = AHasMany::getMapping()->getTables();
			$firstTable = array_shift($tables);

			$query->join($firstTable->getName(), 'hasMany', 'a.id = hasMany.aId')->groupBy(['a.id']);

			$query->andWhere('hasMany.propOfHasManyA', "LIKE", "%" . $filter['hasHasMany'] . "%");
		}

		return parent::filter($query, $filter);
	}
	
}
