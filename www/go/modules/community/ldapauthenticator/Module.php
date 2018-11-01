<?php
namespace go\modules\community\ldapauthenticator;

use go\core\module\Base;
use go\modules\community\ldapauthenticator\model\Authenticator;

class Module extends Base {

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(\go\modules\core\modules\model\Module $model) {
		
		if(!Authenticator::register()) {
			return false;
		}
		
		return parent::afterInstall($model);
	}

}
