<?php

namespace go\core;

use GO\Base\Model\Module;
use go\core\model\User;
use go\core\util\ClassFinder;
use go\core\util\StringUtil;

class StringUtilTest extends \PHPUnit\Framework\TestCase {
	public function testCyrillicSearch() {
		$str = "Гугъл гъл";

		$words = StringUtil::splitTextKeywords($str);

		$this->assertEquals(2 ,count($words));

		$this->assertEquals(mb_strtolower("Гугъл") ,$words[0]);

		$this->assertEquals(mb_strtolower("гъл") ,$words[1]);
	}
}