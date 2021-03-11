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

	public static function mappedValues(Record $record) {
		$cfg = go()->getConfig();
		if(empty($cfg['ldapMapping'])) {
			return [];
		}
		$mapping = $cfg['ldapMapping'];
		foreach($mapping as $local => $ldap) {
			if($ldap instanceof \Closure) {
				$mapping[$local] = $ldap($record);
			} else if(isset($record->{$ldap})) {
				$mapping[$local] = $record->{$ldap}[0];
			} else {
				unset($mapping[$local]);
			}
		}
		return $mapping;
	}


	public static function ldapRecordToUser($username, Record $record, User $user) {

		go()->debug("cn: " . $record->cn[0] ?? "NULL");
		go()->debug("mail: " .$record->mail[0] ?? "NULL");

		if(!isset($record->mail) || !isset($record->mail[0])) {
			throw new \Exception("User '$username' has no 'mail' attribute set. Can't create a user");
		}

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

		$values = self::mappedValues($record);

		if(isset($values['diskQuota'])) $user->disk_quota = $values['diskQuota'];
		if(isset($values['recoveryEmail'])) $user->recoveryEmail = $values['recoveryEmail'];
		if(isset($values['enabled'])) $user->enabled = $values['enabled'];
		if(isset($values['email'])) $user->email = $values['email'];
		if(isset($values['recoveryEmail'])) $user->recoveryEmail = $values['recoveryEmail'];
		if(isset($values['displayName'])) $user->displayName = $values['displayName'];

		unset($values['enabled'], $values['recoveryEmail'], $values['diskQuota'], $values['recoveryEmail'], $values['displayName']);

		if(\go\core\model\Module::findByName('community', 'addressbook')) {

			$phoneNbs = [];
			if(isset($values['workPhone'])){
				$phoneNbs[] = ['number' => $values['workPhone'], 'type' => 'work'];
				unset($values['workPhone']);
			}
			if(isset($values['workFax'])) {
				$phoneNbs[] = ['number' => $values['workFax'], 'type' => 'workfax'];
				unset($values['workFax']);
			}

			if(!empty($phoneNbs)) {
				$values['phoneNumbers'] = $phoneNbs;
			}

			if(isset($values['email'])) {
				$values['emailAddresses'] = [['email' => $values['email']]];
				unset($values['email']);
			}

			$addrAttrs = [];
			if(!empty($values['street'])) $addrAttrs['street'] = $values['street'];
			if(!empty($values['street2'])) $addrAttrs['street2'] = $values['street2'];
			if(!empty($values['zipCode'])) $addrAttrs['zipCode'] = $values['zipCode'];
			if(!empty($values['city'])) $addrAttrs['city'] = $values['city'];
			if(!empty($values['country'])) $addrAttrs['country'] = $values['country'];
			if(!empty($addrAttrs)) {
				$addrAttrs['type'] = 'home';
				$values['addresses'] = $addrAttrs;
			}
			unset($values['street'],$values['street2'],$values['zipCode'],$values['city'],$values['country'] );

			if(!empty($values)) {
				if(isset($blob)) {
					$values['photoBlobId'] = $blob->id;
				}
				$user->setProfile($values);
			}
		}

		return $user;
	}

}
