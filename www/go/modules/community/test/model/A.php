<?php
namespace go\modules\community\test\model;

use go\core\jmap\Entity;
use go\core\orm\CustomFieldsTrait;
use go\core\orm\Mapping;
use go\core\util\DateTime;

/**
 * 
 * 
 */
class A extends Entity {

	use CustomFieldsTrait;
	
	/**
	 * The primary key
	 * 
	 *
	 */
	public ?int $id;
	

	/**
	 *
	 * @var DateTime
	 */
	public \DateTimeInterface $createdAt;
	
	/**
	 *
	 * @var DateTime
	 */
	public \DateTimeInterface $modifiedAt;
	
	/**
	 *
	 * @var string
	 */
	public string $propA;
	
	/**
	 *
	 * @var AHasMany[] $hasMany The has many property models
	 */
	public array $hasMany = [];
	
	/**
	 *
	 * @var AHasOne $hasOne The hasOne property model 
	 */
	public ?AHasOne $hasOne;


	/**
	 * @var AMap[]
	 */
	public ?array $map;
					
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('test_a', 'a')						
						->addArray('hasMany', AHasMany::class, ['id' => 'aId'])
						->addMap('map', AMap::class, ['id' => 'aId'])
						->addHasOne('hasOne', AHasOne::class, ['id' => 'aId']);
	}

}
