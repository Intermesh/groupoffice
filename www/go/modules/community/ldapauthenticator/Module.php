<?php
namespace go\modules\community\ldapauthenticator;

use go\core\auth\DomainProvider;
use go\core\db\Query;
use go\core\module\Base;
use go\modules\community\ldapauthenticator\model\Authenticator;
use go\modules\core\modules\model\Module as CoreModule;

class Module extends Base implements DomainProvider {

	public function getAuthor() {
		return "Intermesh BV";
	}
	
	protected function afterInstall(CoreModule $model) {
		
		if(!Authenticator::register()) {
			return false;
		}
		
		return parent::afterInstall($model);
	}

	public static function getDomainNames() {
		return (new Query)
						->selectSingleValue('name')
						->from('ldapauth_server_domain')
						->all();
	}

}
