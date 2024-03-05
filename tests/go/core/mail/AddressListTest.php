<?php

namespace go\core;

use GO\Base\Model\Module;
use go\core\mail\AddressList;
use go\core\model\User;
use go\core\util\ClassFinder;
use go\core\util\StringUtil;

class AddressListTest extends \PHPUnit\Framework\TestCase {
	public function testParse() {
		$str = '<test@test.org.br>,
	"\'\'natalie typo\'\'" <natalie.typo@test.org.br>,
	"\'Jane Cris Fera\'" <jane.doe@test.org.br>,
	<fabia.doe@test.org.br>,
	<marcelo.doe@test.org.br>,
	<tulio.bar@test.org.br>,
	<rodolfo.foo@test.org.br>';


		$list = new AddressList($str);
		$this->assertCount(7, $list);
		$this->assertEquals($list[1]->getName(), "''natalie typo''");

		$this->assertEquals($list[0]->getName(), "");
		$this->assertEquals($list[0]->getEmail(), "test@test.org.br");
	}

}