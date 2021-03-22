<?php
namespace go\modules\community\ldapauthenticator;

use go\core\auth\DomainProvider;
use go\core\db\Query;
use go\core;
use go\modules\community\addressbook\model\Contact;
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
			$cfg['ldapMapping'] = [
				'enabled' => function($record) {
					//return $record->ou[0] != 'Delivering Crew';
					return true;
				},
				'diskQuota' => function($record) {
					//return 1024 * 1024 * 1024;
					return null;
				},
				'email' => 'mail',
				'recoveryEmail' => 'mail',
				'displayName' => 'cn',
				'firstName' => 'givenname',
				'lastName' => 'sn',
				'initials' => 'initials',

				'jobTitle' => 'title',
				'department' => 'department',
				'notes' => 'info',

//				'addressType' => function($record) {
//					return \go\modules\community\addressbook\model\Address::TYPE_WORK;
//				},
				'street' => 'street',
				'zipCode' => 'postalCode',
				'city' => 'l',
				'state' => 's',
//				'countryCode' => function($record) {
//					return "NL";
//				},

				'homePhone' => 'homePhone',
				'mobile' => 'mobile',
				'workFax' => 'facsimiletelephonenumber',
				'workPhone' => 'telephonenumber',

				'organization' => 'organizationname'
				];
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
		if(isset($values['displayName'])) $user->displayName = $values['name'] = $values['displayName'];

		if(\go\core\model\Module::isInstalled('community', 'addressbook')) {

			$phoneNbs = [];
			if(isset($values['homePhone'])){
				$phoneNbs[] = ['number' => $values['homePhone'], 'type' => 'work'];
			}
			if(isset($values['workPhone'])){
				$phoneNbs[] = ['number' => $values['workPhone'], 'type' => 'work'];
			}
			if(isset($values['mobile'])){
				$phoneNbs[] = ['number' => $values['mobile'], 'type' => 'mobile'];
			}
			if(isset($values['workFax'])) {
				$phoneNbs[] = ['number' => $values['workFax'], 'type' => 'workfax'];
			}

			if(!empty($phoneNbs)) {
				$values['phoneNumbers'] = $phoneNbs;
			}

			if(isset($values['email'])) {
				$values['emailAddresses'] = [['email' => $values['email']]];
			}

			$addrAttrs = [];
			if(!empty($values['street'])) $addrAttrs['street'] = $values['street'];
			if(!empty($values['street2'])) $addrAttrs['street2'] = $values['street2'];
			if(!empty($values['zipCode'])) $addrAttrs['zipCode'] = $values['zipCode'];
			if(!empty($values['city'])) $addrAttrs['city'] = $values['city'];
			if(!empty($values['state'])) $addrAttrs['state'] = $values['state'];
			if(!empty($values['country'])) $addrAttrs['country'] = $values['country'];
			if(!empty($values['countryCode'])) $addrAttrs['countryCode'] = $values['countryCode'];
			if(!empty($values['type'])) $addrAttrs['type'] = $values['addressType'];
			if(!empty($addrAttrs)) {
				$values['addresses'] = [$addrAttrs];
			}

			if(!empty($values['organization'])) {

				$org = Contact::find(['id'])->where([
					'name' => $values['organization'],
					'isOrganization' => true]
				)->single();
				if(!$org) {
					$org = new Contact();
					$org->name = $values['organization'];
					$org->isOrganization = true;
					$org->addressBookId = go()->getSettings()->userAddressBook()->id;
					if(!$org->save())  {
						throw new \Exception("Could not save organization");
					}
				}

				$values['organizationIds'] = [$org->id];
			} else{
				$values['organizationIds'] = [];
			}

			//strip out unsupported properies.
			$props = Contact::getApiProperties();
			$values = array_intersect_key($values, $props);

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
