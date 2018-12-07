<?php

namespace go\core\db;

use PHPUnit\Framework\TestCase;

class TableTest extends TestCase {

	public function testColumnDefinitions() {
		$table = Table::getInstance('cf_ab_companies');
		
		foreach($table->getColumns() as $col) {
			var_dump($col->name . ' '.$col->getCreateSQL());
			$this->assertStringStartsWith($col->dbType, $col->getCreateSQL());
		}
	}

}

