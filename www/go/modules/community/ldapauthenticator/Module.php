<?php
namespace go\modules\community\ldapauthenticator;

use go\core\auth\DomainProvider;
use go\core\db\Query;
use go\core;
use go\modules\community\ldapauthenticator\model\Authenticator;
use go\core\model\Module as CoreModule;
use go\core\ldap\Record;
use go\core\model\User;
use go\core\fs\Blob;

class Module extends core\Module implements DomainProvider {

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


	public static function ldapRecordToUser($username, Record $record, User $user) {

		go()->debug("cn: " . $record->cn[0] ?? "NULL");
		go()->debug("mail: " .$record->mail[0] ?? "NULL");

		$user->username = $username;

		if(!empty($record->jpegPhoto[0])) {
			$blob = Blob::fromString($record->jpegPhoto[0]);
			$blob->type = 'image/jpeg';
			$blob->name = $username . '.jpg';
			if(!$blob->save()) {
				throw new \Exception("Could not save blob");
			}
			$user->avatarId = $blob->id;
		}

		$user->displayName = $record->cn[0];		
		$user->email = $record->mail[0];
		$user->recoveryEmail = isset($record->mail[1]) ? $record->mail[1] : $record->mail[0];		

		return $user;
	}

}
