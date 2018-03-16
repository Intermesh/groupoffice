<?php
namespace go\modules\community\imapauthenticator;

use go\core\module\Base;
use go\modules\community\imapauthenticator\model\Authenticator;

class Module extends Base {

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall() {
		
		if(!Authenticator::register()) {
			return false;
		}
		
		return parent::afterInstall();
	}

}