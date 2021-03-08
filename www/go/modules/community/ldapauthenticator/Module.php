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
			if(is_callable($ldap)) {
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

		if(\go\core\model\Module::findByName('community', 'addressbook')) {
			$attrs = new \stdClass();
			if(isset($values['firstName'])) $attrs->firstName = $values['firstName'];
			if(isset($values['middleName'])) $attrs->firstName = $values['middleName'];
			if(isset($values['lastName'])) $attrs->lastName = $values['lastName'];
			if(isset($values['prefixes'])) $attrs->prefixes = $values['prefixes'];
			if(isset($values['department'])) $attrs->department = $values['department'];
			if(isset($values['sex'])) $attrs->gender = $values['sex'];

			$phoneNbs = [];
			if(isset($values['workPhone'])) $phoneNbs[] = (new \go\modules\community\addressbook\model\PhoneNumber())->setValues(['number' => $values['workPhone'], 'type' => 'work']);
			if(isset($values['workFax'])) $phoneNbs[] = (new \go\modules\community\addressbook\model\PhoneNumber())->setValues(['number' => $values['workFax'], 'type' => 'workfax']);
			if(!empty($phoneNbs)) $attrs->phoneNumbers = $phoneNbs;

			if(isset($values['email'])) $attrs->emailAddresses = [(new \go\modules\community\addressbook\model\EmailAddress())->setValues(['email' => $values['email']])];

			$addrAttrs = [];
			if(!empty($values['street'])) $addrAttrs['street'] = $values['street'];
			if(!empty($values['zipCode'])) $addrAttrs['zipCode'] = $values['zipCode'];
			if(!empty($values['city'])) $addrAttrs['city'] = $values['city'];
			if(!empty($values['country'])) $addrAttrs['country'] = $values['country'];
			if(!empty($addrAttrs)) {
				$addrAttrs['type'] = 'home';
				if(!empty($phoneNbs)) $attrs->addresses = [(new \go\modules\community\addressbook\model\Address())->setValues($addrAttrs)];
			}
			$attrs = (array)$attrs;
			if(!empty($attrs)) {
				$user->setProfile($attrs);
			}
		}

		return $user;
	}

}
