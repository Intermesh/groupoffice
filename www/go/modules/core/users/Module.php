<?php
namespace go\modules\core\users;

use go\core\auth\Password;
use go\core\module\Base;

class Module extends Base {
	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall() {		
		if(!Password::register()) {
			return false;
		}
		return parent::afterInstall();
	}
}
