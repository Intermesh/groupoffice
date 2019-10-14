<?php

namespace go\modules\community\serverclient;

use go\modules\core\users\model\User;
use \go\modules\community\serverclient\model\MailDomain;
use go\core\module\Base;


class Module extends Base {
	public function getAuthor(){
		return 'Intermesh BV';
	}

	public function defineListeners() {
		User::on(\go\core\orm\Entity::EVENT_SAVE, static::class, 'onSaveUser');
	}
	
	public static function getDomains(){
		if(empty(\GO::config()->serverclient_domains)) {
			return array();
		}
		if(is_array(\GO::config()->serverclient_domains)) {
			return \GO::config()->serverclient_domains;
		}
		return array_map('trim',explode(",", \GO::config()->serverclient_domains));
	}

	public static function onSaveUser(User $user) {
		
		if($user->isNew()) {
			return;
		}
		
		$domains = self::getDomains();
		
		if(!empty($user->plainPassword()) && !empty($domains)) {
			$postfixAdmin = new MailDomain($user->plainPassword());
			foreach ($domains as $domain) {
				$postfixAdmin->setMailboxPassword($user, $domain);
			}
		}
	}

}
