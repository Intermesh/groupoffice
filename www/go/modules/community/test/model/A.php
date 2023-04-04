<?php
namespace go\modules\community\test\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;
use go\core\util\DateTime;

/**
 * 
 * 
 */
class A extends Entity {
	
	/**
	 * The primary key
	 * 
	 * @var int 
	 */
	public $id;
	

	/**
	 *
	 * @var DateTime
	 */
	public $createdAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public $modifiedAt;
	
	/**
	 *
	 * @var string
	 */
	public $propA;
	
	/**
	 *
	 * @var AHasMany[] $hasMany The has many property models
	 */
	public $hasMany = [];	
	
	/**
	 *
	 * @var AHasOne $hasOne The hasOne property model 
	 */
	public $hasOne;


	public $map;
					
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('test_a', 'a')						
						->addArray('hasMany', AHasMany::class, ['id' => 'aId'])
						->addMap('map', AMap::class, ['id' => 'aId'])
						->addHasOne('hasOne', AHasOne::class, ['id' => 'aId']);
	}

}
