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

//		$str = '"\'\'natalie typo\'\'" <natalie.typo@test.org.br>';

		$list = new AddressList($str);

		var_dump($list->toArray());
	}

}