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
			->addTable('test_b', 'b')
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
	
}