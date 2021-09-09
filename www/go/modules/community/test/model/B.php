<?php
namespace go\modules\community\test\model;

use go\core\orm\Query;
use go\core\validate\ErrorCode;

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


	public $testSaveOtherModel = false;

	public $userId;
	
	
	protected static function defineMapping() {
		$mapping = parent::defineMapping()
			->addTable('test_b', 'b', ['id' => 'id'], null, ['userId' => go()->getUserId()])
			->addQuery((new Query())->select("SUM(b.id) AS sumOfTableBIds")->join('test_b', 'bc', 'bc.id=a.id')->groupBy(['a.id']));
		
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
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add("propA", function(Query $query, $value, array $filter) {
							$query->andWhere('propA', 'LIKE', $filter['propB'] . "%");
						})
						->add("propB", function(Query $query, $value, array $filter) {
							$query->andWhere('propB', 'LIKE', $filter['propB'] . "%");
						})
						->add("hasHasMany", function(Query $query, $value, array $filter) {
							$tables = AHasMany::getMapping()->getTables();
							$firstTable = array_shift($tables);

							$query->join($firstTable->getName(), 'hasMany', 'a.id = hasMany.aId')->groupBy(['a.id']);

							$query->andWhere('hasMany.propOfHasManyA', "LIKE", "%" . $filter['hasHasMany'] . "%");
						});
	}

	protected function internalSave() {

		if($this->testSaveOtherModel) {
			$other = new self;
			$other->propA = 'other';
			$other->propB = 'other';
			if(!$other->save()) {
				$this->setValidationError('testSaveOtherModel', ErrorCode::GENERAL, 'Could not save other model: '. var_export($other->getValidationErrors(), true));
			}
		}

		return parent::internalSave();
	}
	
}
