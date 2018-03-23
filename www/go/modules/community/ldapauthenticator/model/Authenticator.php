<?php
namespace go\modules\community\ldapauthenticator\model;

use Exception;
use go\core\auth\model\User;
use go\core\auth\PrimaryAuthenticator;

/**
 * LDAP Authenticator
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @author Merijn Schering <mschering@intermesh.nl> 
 */
class Authenticator extends PrimaryAuthenticator {
	
	public static function id() {
		return "ldap";
	}

	public static function isAvailableFor($username) {		
		return static::findServer($username) != false;
	}
	
	/**
	 * 
	 * @param string $email
	 * @return LdapAuthServer|boolean
	 */
	private static function findServer($email) {
		$adPos = strpos($email, '@');
		if(!$adPos) {
			return false;
		}
		
		$domain = substr($email, $adPos + 1);
		
		return LdapAuthServer::find()
						->join('ldapauth_server_domain', 'd', 's.id = d.serverId')
						->where(['d.name' => $domain])
						->orWhere(['d.name' => '*'])
						->single();
	}	
	
	public function authenticate($username, $password) {
		
		$server = $this->findServer($username);
		
		$connection = new \go\core\ldap\Connection();
		if(!$connection->connect($server->getUri())) {
			throw new \Exception("Could not connect to LDAP server");
		}
		if($server->encryption == 'tls') {
			if(!$connection->startTLS()) {
				throw new \Exception("Couldn't enable TLS");
			}
		}
		
		$record = \go\core\ldap\Record::find($connection, $server->peopleDN, $server->usernameAttribute . "=" . explode('@', $username)[0])->fetch();
		
		if(!$record) {
			return false;
		}
		
		if(!$connection->bind($record->getDn(), $password)) {
			return false;
		}
		
		$user = User::find()->where(['username' => $username])->single();
		if(!$user) {
			$user = $this->createUser($username, $record);
		}
		
		return $user;
	
	}	
	
	private function createUser($email, \go\core\ldap\Record $record) {
		$user = new User();
		$user->displayName = $record->cn[0];
		$user->username = $email;
		$user->email = $record->mail[0];
		$user->recoveryEmail = isset($record->mail[1]) ? $record->mail[1] : $record->mail[0];		
		
		if(!$user->save()) {
			throw new Exception("Could not save user after succesful IMAP login");
		}
		
		return $user;
	}

}