<?php


namespace GO\Ldapauth;
use GO;

class Authenticator {


	private static $_mapping = false;

	public static function getMapping() {
		if (self::$_mapping) {
			return self::$_mapping;
		}

		$conf = str_replace('config.php', 'ldapauth.config.php', \GO::config()->get_config_file());

		if (file_exists($conf)) {
			require($conf);
			self::$_mapping = $mapping;
		} else {
			self::$_mapping = array(
					'exclude' => new Mapping\Constant(false),
					'enabled' => new Mapping\Constant('1'),
					'username' => 'uid',
					//'password' => 'userpassword',
					'first_name' => 'givenname',
					'middle_name' => 'middlename',
					'last_name' => 'sn',
					'initials' => 'initials',
					'title' => 'title',
					'sex' => 'gender',
					'birthday' => 'birthday',
					'email' => 'mail',
					'company' => 'o',
					'department' => 'ou',
					'function' => 'businessrole',
					'home_phone' => 'homephone',
					'work_phone' => 'telephonenumber',
					'fax' => 'homefacsimiletelephonenumber',
					'cellular' => 'mobile',
					'country' => 'homecountryname',
					'state' => 'homestate',
					'city' => 'homelocalityname',
					'zip' => 'homepostalcode',
					'address' => 'homepostaladdress',
					'currency' => 'gocurrency',
					'max_rows_list' => 'gomaxrowslist',
					'timezone' => 'gotimezone',
					'start_module' => 'gostartmodule',
					'theme' => 'gotheme',
					'language' => 'golanguage',
			);
		}

		return self::$_mapping;
	}
	
	
	public function getUserSearchQuery($username='*'){
		$mapping = $this->getMapping();
		
		if (!empty(GO::config()->ldap_search_template))
			$query = str_replace('{username}', $username, GO::config()->ldap_search_template);
		else
			$query = $mapping['username'] . '=' . $username;
		
		return $query;
	}

	public function authenticate($username, $password) {

		if (empty(\GO::config()->ldap_peopledn)) {
			\GO::debug('LDAPAUTH: Aborting because the following required value is not set: $config["ldap_peopledn"]');
			return true;
		}

		$record = \GO\Ldapauth\Model\Person::findByUsername($username);


		if (!$record) {
			\GO::debug("LDAPAUTH: No LDAP entry found for " . $username);
			//return true here because this should not block normal authentication
			return true;
		}
		
		//$authenticated = $ldapConn->bind($record->getDn(), $password);
		if (!$record->authenticate($password)) {
			$str = "LOGIN FAILED for user: \"" . $username . "\" from IP: ";
			if(isset($_SERVER['REMOTE_ADDR']))
				$str .= $_SERVER['REMOTE_ADDR'];
			else
				$str .= 'unknown';
			
			\GO::infolog($str);
			
			return false;
		}
		
		\GO::debug("LDAPAUTH: LDAP authentication SUCCESS for " . $username);


		


		if(!empty(GO::config()->ldap_create_mailbox_domains)){

			if(!GO::modules()->serverclient)
				throw new \Exception("The serverclient module must be installed and configured when using \$config['GO::config()->ldap_create_mailbox_domains']. See https://www.group-office.com/wiki/Mailserver#Optionally_install_the_serverclient");

			$_POST['serverclient_domains']=GO::config()->ldap_create_mailbox_domains;
		}else
		{
			GO::debug("LDAPAUTH: Found LDAP entry found for " . $username);
			
//			GO::debug($record->getAttributes());
		}


		$user = $this->syncUserWithLdapRecord($record, $password);
		if(!$user){		
			return false;
		}

		try{
			$this->_checkEmailAccounts($user, $password);
		}catch(\Exception $e){
//				GO::debug("LDAPAUTH: Failed to create or update e-mail account!\n\n".(string) $e);
			trigger_error("LDAPAUTH: Failed to create or update e-mail account for user ".$user->username."\n\n".$e->getMessage());
		}




	}
	
	/**
	 * 
	 * @param \GO\Base\Ldap\Record $user
	 * @param type $password
	 * @return \GO\Base\Model\User
	 */
	public function syncUserWithLdapRecord(\GO\Base\Ldap\Record $record, $password = null) {
		
		//disable password validation because we can't control the external passwords
		\GO::config()->password_validate=false;
		

		$attr = $this->getUserAttributes($record);
		
		
		// ldap data $attr
		
		if(!empty($attr['exclude'])){
			\GO::debug("LDAPAUTH: User is excluded from LDAP by mapping!");
			
			return false;
		}
		
		unset($attr['exclude']);
		
		try {
			$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $attr['username']);
			if ($user) {
				\GO::debug("LDAPAUTH: Group-Office user already exists.");
				if (isset($password) && !$user->checkPassword($password)) {
					\GO::debug('LDAPAUTH: LDAP password has been changed. Updating Group-Office database');

					$user->password = $password;
				}

				if (empty(\GO::config()->ldap_auth_dont_update_profiles)) {
					//never update the e-mail address because the user
					//can't change it to something invalid.



					if ($this->validateUserEmail($record, $user->email))
						unset($attr['email']);

					$user->setAttributes($attr);
					$user->cutAttributeLengths();

					\GO::debug('LDAPAUTH: updating user profile');
					\GO::debug($attr);

					$this->_updateContact($user, $attr);
				}else {
					\GO::debug('LDAPAUTH: Profile updating from LDAP is disabled');
				}

				if(!$user->save()){
					throw new \Exception("Could not save user: ".implode("\n", $user->getValidationErrors()));
				}
			} else {
				\GO::debug("LDAPAUTH: Group-Office user does not exist. Attempting to create it.");
				\GO::debug($attr);

				$user = new \GO\Base\Model\User();
				$user->setAttributes($attr);
				$user->cutAttributeLengths();
				$user->password = $password;

				if(!$user->save()){
					throw new \Exception("Could not save user: ".implode("\n", $user->getValidationErrors()));
				}
				if (!empty(\GO::config()->ldap_groups))
					$user->addToGroups(explode(',', \GO::config()->ldap_groups));

				$this->_updateContact($user, $attr);

				$user->checkDefaultModels();
			}
		
		} catch (\Exception $e) {
				\GO::debug('LDAPAUTH: Failed creating user ' .
								$attr['username'] .
								' Exception: ' .
								$e->getMessage(), E_USER_WARNING);
				
				return false;
			}
		return $user;
	}

	private function _updateContact($user, $attributes) {
		
		
		
		$contact = $user->createContact();
		if ($contact) {
			\GO::debug('LDAPAUTH: updating user contact');
			if(!empty($attributes['photo'])) {
				$data = $attributes['photo'];
				unset($attributes['photo']);
			}
			$contact->setAttributes($attributes);
			
			if (isset($data)) {
				$f = \GO\Base\Fs\File::tempFile(uniqid('ldap_'), "jpg");
				$f->putContents($data);

				$contact->setPhoto($f);
			}
			$contact->cutAttributeLengths();
			$contact->skip_user_update=true;

			if (!empty($attributes['company'])) {
				$company = \GO\Addressbook\Model\Company::model()->findSingleByAttributes(array(
						'addressbook_id' => $contact->addressbook_id,
						'name' => $attributes['company']
								));				

				if (!$company) {
					\GO::debug('LDAPAUTH: creating company for contact');
					$company = new \GO\Addressbook\Model\Company();
					$company->name = $attributes['company'];
					$company->addressbook_id = $contact->addressbook_id;
					$company->cutAttributeLengths();
					$company->save();
				} else {
					\GO::debug('LDAPAUTH: found existing company for contact');
				}
				$contact->company_id = $company->id;
			}

			$contact->save();
			if(isset($f)) {
				$f->delete();
			}
		}
	}

	private function _checkEmailAccounts(\GO\Base\Model\User $user, $password) {
		if (\GO::modules()->isInstalled('email')) {

			$arr = explode('@', $user->email);
			$mailbox = trim($arr[0]);
			$domain = isset($arr[1]) ? trim($arr[1]) : '';

			$imapauth = new \GO\Imapauth\Authenticator();
			$config = $imapauth->config = $imapauth->getDomainConfig($domain);

			if (!$config) {
				\GO::debug('LDAPAUTH: No E-mail configuration found for domain: ' . $domain);
				return false;
			}
			
			if(empty($config['create_email_account'])){
				\GO::debug('LDAPAUTH: E-mail account creation disabled for domain: ' . $domain);
				return false;
			}
				

			\GO::debug('LDAPAUTH: E-mail configuration found. Creating e-mail account');
			$imapUsername = empty($config['ldap_use_email_as_imap_username']) ? $user->username : $user->email;

			if (!$imapauth->checkEmailAccounts($user, $config['host'], $imapUsername, $password)) {
				$imapauth->createEmailAccount($user, $config, $imapUsername, $password);
			}
		}
	}

	public function getUserAttributes(\GO\Base\Ldap\Record $record) {

		$userAttributes = array();

		$mapping = $this->getMapping();

		$lowercase = $record->getAttributes();

		foreach ($mapping as $userAttribute => $ldapMapping) {
			if (!empty($ldapMapping)) {
				if (!is_string($ldapMapping)) {
					$value = $ldapMapping->getValue($record);
				} else {
					$ldapMapping = strtolower($ldapMapping);
					if (!empty($lowercase[$ldapMapping])) {
						$value = $lowercase[$ldapMapping][0];
					} else {
						continue;
					}
				}

				$userAttributes[$userAttribute] = $value;
			}
		}

		if (!empty(\GO::config()->ldap_use_uid_with_email_domain))
			$userAttributes['email'] = $userAttributes['username'] . '@' . \GO::config()->ldap_use_uid_with_email_domain;
		
		
		//sometimes users mapped the password. Unset it here to make sure the hash is not used for password in the user.
		unset($userAttributes['password']);
		
		//make sure there's no id
		unset($userAttributes['id']);

		return $userAttributes;
	}

	/**
	 * Checks if an e-mail address is present in the LDAP directory
	 * 	 
	 * @param \GO\Base\Ldap\Record $record
	 * @param StringHelper $email
	 * @param array $validAddresses
	 * @return type 
	 */
	public function validateUserEmail(\GO\Base\Ldap\Record $record, $email, &$validAddresses = array()) {

		$mapping = $this->getMapping();

		$lowercase = $record->getAttributes();

		if (isset($lowercase[$mapping['email']]))
			$val = $lowercase[$mapping['email']];
		else
			return false;

		$validAddresses = array();
		for ($i = 0; $i < count($val); $i++) {
			$validAddresses[] = strtolower($val[$i]);
		}

		if (!empty(\GO::config()->ldap_use_uid_with_email_domain)) {

			$default = strtolower($lowercase[$mapping['username']][0]) . '@' . \GO::config()->ldap_use_uid_with_email_domain;

			if (!in_array($default, $validAddresses)) {
				$validAddresses[] = $default;
			}
		}

		if (!in_array(strtolower($email), $validAddresses)) {
			return false;
		}else		
		{
			return true;
		}
	}
}
