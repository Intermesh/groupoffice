<?php

namespace go\core\db;

use PHPUnit\Framework\TestCase;

class TableTest extends TestCase {

	public function testColumnDefinitions() {
		$table = Table::getInstance('core_user');
		
		foreach($table->getColumns() as $col) {
			$this->assertStringStartsWith($col->dataType, $col->getCreateSQL());
		}
	}

}

