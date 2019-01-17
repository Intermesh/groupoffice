<?php

namespace GO\Serverclient;

use go\modules\core\users\model\User;

class ServerclientModule extends \GO\Base\Module{
	
//	public static function initListeners() {
//		
//		\GO\Base\Model\User::model()->addListener("save", "GO\Serverclient\ServerclientModule", "saveUser");
//		
//		return parent::initListeners();
//	}

	public static function defineListeners() {
		//User::on(\go\core\orm\Entity::EVENT_SAVE, static::class, 'onSaveUser');
		//User::on(\go\core\orm\Entity::EVENT_MAPPING, static::class, 'onUserMap');
	}
	
	public static function getDomains(){
		return empty(\GO::config()->serverclient_domains) ? array() : array_map('trim',explode(",", \GO::config()->serverclient_domains));
	}
	
	public static function onUserMap(\go\core\orm\Mapping $mapping) {
		//$mapping->addProperty('serverDomains');
	}
	
	public static function onSaveUser(User $user) {
		
		if(!isset($_POST['serverclient_domains']))
			$_POST['serverclient_domains']=array();
		
		$domains = $user->isNew() ? $_POST['serverclient_domains'] : self::getDomains();
		
		if(!empty($domains)){
			$tt = new \GO\Serverclient\Model\MailDomain($domains);
		}
	}

}
