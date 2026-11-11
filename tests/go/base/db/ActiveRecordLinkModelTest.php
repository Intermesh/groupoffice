<?php
namespace go\base\db;

use GO\Base\Db\FindParams;
use GO\Base\Model\Group;
use PHPUnit\Framework\TestCase;

/**
 * A class that isn't a model but takes any constructor arguments
 */
class ActiveRecordLinkModelTestGadget
{
	public static bool $created = false;

	public function __construct(...$args)
	{
		self::$created = true;
	}
}

/**
 * The linkModel find parameter is a class name that is created, so it must be a model.
 */
class ActiveRecordLinkModelTest extends TestCase
{
	public function testLinkModelMustBeAModel(): void
	{
		ActiveRecordLinkModelTestGadget::$created = false;
		$params = FindParams::newInstance()->linkModel(ActiveRecordLinkModelTestGadget::class, 'groupId', 1);

		try {
			Group::model()->find($params);
		} catch (\Throwable $e) {
		}

		$this->assertFalse(ActiveRecordLinkModelTestGadget::$created, "The class may not be created");
		$this->assertTrue(isset($e) && $e->getMessage() === "Invalid linkModel");
	}

	public function testManyManyRelationStillWorks(): void
	{
		$users = Group::model()->findByPk(\go\core\model\Group::ID_ADMINS, false, true)->users;
		$this->assertNotEmpty($users->fetchAll());
	}
}
