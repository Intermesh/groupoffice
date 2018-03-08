<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GO\Ldapauth\Model;
use GO;

class Person extends GO\Base\Ldap\Record {

	protected $userRecord=null;
	
	protected $username = 'unknown';
	
	/**
	 * Extra ldap var to display in settings dialog
	 * This will define the fields and the rendering structure for the dialog.
	 * Configure the field in the config.php by setting  $config['ldap_person_fields']
	 * The order is important!
	 * - 1st argument = one if following data types
	 *   - *text: Will render a textfield
	 *   - *textarea: Will render a textarea
	 *   - header: Displays a heading and a horizontal line
	 *   - *checkbox: Renders a textbox
	 *   - display: Renders text just for display
	 *   - tab: Add a new tab (every following component will be on this tab)
	 *   - *list: Renders a list for adding multiple value in 1 field
	 * - 2nd argument = key needed for the above components with a * the key should correspond to the LDAP key
	 * - Next argument are component settings
	 *   - label: field label
	 *   - text: value for 'display', 'header' and 'tab' components
	 *   - offValue: for 'checkbox' what is posted when the checkbox is off
	 *   - onValue: for 'checkbox' what is posted when the checkbox is on
	 * 
	 * Example:
	 * ~~~
	 * array(
	 *		array('tab','text'=>'Forwarding'),
	 *		array('checkbox', 'forwarding_enable','label' => 'Enable forwarding'),
	 *		array('checkbox', 'forwarding_leave', 'label' => 'Do not leave copy on server'),
	 *		array('list', 'mailForwardingAddress', 'label' => 'Forward to the following addresses'),
	 *		array('tab','text'=>'Vacation'),
	 *		array('checkbox', 'vacation_enable', 'label' => 'Enable Vacation Message'),
	 *		array('textarea', 'mailReplyText', 'label' => 'Message'),
	 *		array('tab','text'=>'Spam'),
	 *		array('checkbox', 'spamFilterStatus', 'label'=>'Enable spam report', 'onValue'=>'enable', 'offValue'=>'disable'),
	 *		array('display', 'text'=>'Filter unsolicited commercial Email. Please choose level of protection.'),
	 *		array('checkbox', 'spamReportStatus', 'label'=>'Enable spam filter', 'onValue'=>'enable', 'offValue'=>'disable'),
	 *		array('list', 'blackListAddress', 'label'=>'Blacklist'),
	 *		array('list', 'whiteListAddress', 'label'=>'Whitelist'),
	 * )
	 * ~~~
	 * 
	 * @var array extra variable mapping 
	 * @see TYPE constants
	 */
	public static function getExtraVars() {
		$conf = GO::config()->get_config_file();
		if (!file_exists($conf))
			$conf = str_replace('config.php', 'ldapauth.config.php', GO::config()->get_config_file());
		if (file_exists($conf)) {
			require($conf);
			if(isset($config['ldap_person_fields']))
				return $config['ldap_person_fields'];
		}
		return array();
	}
	
	/**
	 * Get an LDAP person record by username
	 * @param StringHelper $username just the username
	 * @return \GO\Ldapauth\Model\Person
	 */
	public static function findByUsername($username) {

		$peopleDn = \GO\Ldapauth\LdapauthModule::getPeopleDn($username);
		
		if(empty($peopleDn)){
			return false;
		}
		
		$mapping = self::getMapping();
		$query = $mapping['username'] . '=' . $username;

		$person = self::find($query, $peopleDn);
		GO::debug("LDAPAUTH: Loaded $username!");
		
		if(!empty($person))
			$person->username = $username;
		return $person;
	}
	
	public function authenticate($password) {
		if($this->_ldapConn->bind($this->getDn(), $password))
			return true;
		
		GO::debug("LDAP: authentication FAILED for " . $this->getDn());
		GO::session()->logout();
		return false;
	}

	/**
	 * Change the password of the ldap user account
	 * @param $oldpass the current password to check
	 * @param $newpass the new password
	 * @retrun boolean true when password was changed
	 */
	public function changePassword($oldpass,$newpass) {
		
		if(!empty($newpass) && isset($oldpass)) {
			if($this->authenticate($oldpass)) {
				$mapping = self::getMapping();
				$query = $mapping['username'] . '=' . $this->username.','.\GO\Ldapauth\LdapauthModule::getPeopleDn($this->username);
				$this->_ldapConn->bind(GO::config()->ldap_user, GO::config()->ldap_pass); // become LDAP root
				return @ldap_modify($this->_ldapConn->getLink(), $query, array('userpassword' => $this->encodePassword($newpass)));
				
				$this->_ldapConn->bind($this->getDn(), $oldpass);
			}
		}
		return false;
	}
	
	private function encodePassword($password) {
		return "{SHA}" . base64_encode( pack( "H*", sha1( $password ) ) );
	}
	
	public static function getMapping() {
		if (self::$_mapping) {
			return self::$_mapping;
		}

		$conf = str_replace('config.php', 'ldapauth.config.php', GO::config()->get_config_file());

		if (file_exists($conf)) {
			require($conf);
			self::$_mapping = $mapping;
		} else {
			self::$_mapping = array(
					'exclude' => new \GO\Ldapauth\Mapping\Constant(false),
					'enabled' => new \GO\Ldapauth\Mapping\Constant('1'),
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
	
}