<?php

namespace go\modules\community\history\model;

use go\core\util\DateTime;
use go\modules\community\test\model\AHasMany;
use go\modules\community\test\model\B;
use PHPUnit\Framework\TestCase;

class LogEntryTest extends TestCase {

	public function testLog() {
		$entity = new B();
		$entity->propA = "string 1";
		$entity->propB = "string 2";
		$entity->createdAt = new DateTime();

		//Directly access by offset
		for($i = 0; $i < 10000; $i++) {
			$entity->hasMany[$i] = new AHasMany($entity);
			$entity->hasMany[$i]->propOfHasManyA = "string 5";
		}

		$this->assertEquals(true, $entity->save());
	}
}