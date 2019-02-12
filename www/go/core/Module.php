<?php
namespace go\core;

use go\core\model;
use go\core\module\Base;

class Module extends Base {
	public function getAuthor() {
		return "Intermesh BV";
	}
	
	
	public static function getName() {
		return "core";
	}
	
	public static function getPackage() {
		return "core";
	}
	
	public function getSettings() {		
		return model\Settings::get();
	}
}
