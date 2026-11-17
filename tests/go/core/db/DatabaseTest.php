<?php

namespace go\core\db;

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase {

	public function testIsMariaDBAndVersion()
	{
		$version = go()->getDbConnection()->query("SELECT VERSION()")->fetchColumn();
		$db = new Database();

		// VERSION() returns "10.11.19-MariaDB-0+deb12u1" on MariaDB
		$this->assertSame(stripos($version, 'mariadb') !== false, $db->isMariaDB());

		// getVersion() strips the MariaDB suffix
		$this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $db->getVersion());
	}
}
