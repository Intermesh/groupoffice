<?php

namespace go\core;

use GO\Base\Model\Module;
use go\core\exception\JsonPointerException;
use go\core\model\User;
use go\core\orm\EntityTest;
use go\core\util\ClassFinder;
use go\core\util\JSON;
use go\core\util\StringUtil;
use go\modules\community\addressbook\model\ContactTest;
use go\modules\community\test\model\AMap;

class JSONTest extends \PHPUnit\Framework\TestCase {
	public function testPatchWithEntity() {

		$entityTest = new EntityTest("entitytest2");
		$a = $entityTest->internalTestMap();

		$keys = array_keys($a->map);

		$val = JSON::get($a, "/map/" . $keys[0]);

		$this->assertInstanceOf(AMap::class, $val);

		// should work with or without leading slash
		JSON::patch($a, ["map/" . $keys[0] . "/description" => "patched link to a3"]);

		$val = JSON::get($a, "/map/" . $keys[0] . "/description");

		$this->assertEquals("patched link to a3", $val);
	}

	public function testPatch() {
		$doc = [
			"a/b" => [
				"33" => "test"
			],
			"m~n" => ["foo", "bar"]
		];

		$val = JSON::get($doc, "/a~1b/33");
		$this->assertEquals("test", $val);

		$val = JSON::get($doc, "/m~0n/0");
		$this->assertEquals("foo", $val);

		$doc = JSON::patch($doc, [
			"/a~1b/34" => "test2",
			"/m~0n" => ['apple', 'orange']
		]);

		$val = JSON::get($doc, "/a~1b/34");
		$this->assertEquals("test2", $val);

		$val = JSON::get($doc, "/m~0n/0");
		$this->assertEquals("apple", $val);
	}

	public function testPointerException()
	{
		$this->expectException(JsonPointerException::class);
		$doc = [
			"a/b" => [
				"33" => "test"
			],
			"m~n" => ["foo", "bar"]
		];

		$doc = JSON::patch($doc, [
			"/a~1b/34/test" => "test2"
		]);
	}

}