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

	public function testXSS() {

		$xss[] = '<script>alert("XSS");</script>';
		$xss[] = '<script>alert(\'XSS\');</script>';
		$xss[] = '<script>alert(\'XSS\')</script>';
		$xss[] = '<<script>alert("XSS");//<</script>';
		$xss[] = '<sCripT>alert("XSS")</scRipt>';
		$xss[] = '<scr<script>ipt>alert(\'XSS\')</script>';
		$xss[] = '<sCripT>alert(\'XSS\')</scRipt>';
		$xss[] = '<img src="/" onerror="alert(\'XSS\')"/>';
		$xss[] = '<img src=x onMouseOver=alert(\'XSS\')>';
		$xss[] = '<svg/onload=eval("ale"+"rt")(`XSS${alert`XSS`}`)>';
		$xss[] = '<img src=\'nevermind\' onerror="alert(\'XSS\');" />';
		$xss[] = '<< script>alert("XSS");//<</ script>';
		$xss[] = '<svg/onload=alert(\'XSS\')>';
		$xss[] = 'div.innerHTML = \'<script deferred>alert("XSS");</script>\';';
		$xss[] = '<img src="aaa" onerror=alert(\'xxs\')>';
		$xss[] = '<body onload="alert(\'XSS\')">';


		foreach($xss as $x) {
			$result = StringUtil::detectXSS($x);
			$this->assertEquals(true, $result);
		}

		$false[] = '<img alt="Download on the App Store" height="40" src="">';

		foreach($false as $x) {
			$result = StringUtil::detectXSS($x);
			$this->assertEquals(false, $result);
		}
	}
}