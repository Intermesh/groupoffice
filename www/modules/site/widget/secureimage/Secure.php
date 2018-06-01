<?php
namespace GO\Site\Widget\Secureimage;

include_once dirname(__FILE__) . '/assets/securimage.php';

class Secure extends \Securimage {
	private static $_self = null;
	public static function instance() {
		if(self::$_self===null)
			self::$_self = new self();
		return self::$_self;
	}
}
