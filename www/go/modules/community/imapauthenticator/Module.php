<?php
namespace go\modules\community\imapauthenticator;

use go\core\auth\DomainProvider;
use go\core\db\Query;
use go\core;
use go\modules\community\imapauthenticator\model\Authenticator;
use go\core\model\Module as CoreModule;

class Module extends core\Module implements DomainProvider {
	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}
	
	protected function afterInstall(CoreModule $model): bool
	{
		
		if(!Authenticator::register()) {
			return false;
		}
		
		return parent::afterInstall($model);
	}
	
	public static function getDomainNames(): array
	{
		return (new Query)
						->selectSingleValue('name')
						->from('imapauth_server_domain')
						->all();
	}

}
