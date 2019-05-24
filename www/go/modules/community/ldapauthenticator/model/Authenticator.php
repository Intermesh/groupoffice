<?php
namespace go\modules\community\ldapauthenticator\model;

use Exception;
use go\modules\core\users\model\User;
use go\core\auth\PrimaryAuthenticator;
use go\core\ldap\Connection;
use go\core\ldap\Record;
use GO\Email\Model\Account;

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
		
		list($username, $domain) = self::splitUserName($username);
		
		return static::findServer($domain) != false;
	}
	
	private static function splitUserName($username) {
		$arr = explode('@', $username);
		if(count($arr) !== 2) {
			return [$username, ""];
		} else
		{
			return $arr;
		}
	}
	
	/**
	 * 
	 * @param string $email
	 * @return Server|boolean
	 */
	private static function findServer($domain) {		
		
		return Server::find()
						->join('ldapauth_server_domain', 'd', 's.id = d.serverId')
						->where(['d.name' => $domain])
						->orWhere(['d.name' => '*'])
						->single();
	}	
	
	public function authenticate($username, $password) {
		
		list($ldapUsername, $domain) = $this->splitUserName($username);

		$server = $this->findServer($domain);
		if($server->loginWithEmail) {
			$ldapUsername = $username;
		}
		$connection = new Connection();
		if(!$connection->connect($server->getUri())) {
			throw new \Exception("Could not connect to LDAP server");
		}
		if(!$server->ldapVerifyCertificate) {
			$connection->setOption(LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
		}
		if($server->encryption == 'tls') {
			if(!$connection->startTLS()) {
				throw new \Exception("Couldn't enable TLS: " . $connection->getError());
			}			
		}
		
		if (!empty($server->username)) {			
			
			if (!$connection->bind($server->username, $server->password)) {				
				throw new \Exception("Invalid password given for '".$server->username."'");
			} else
			{
				GO()->debug("Authenticated with user '" . $server->username . '"');
			}
		}
		
		$record = Record::find($connection, $server->peopleDN, $server->usernameAttribute . "=" . $ldapUsername)->fetch();
		
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
		
		foreach($server->groups as $group) {
			$user->addGroup($group->groupId);
		}
		if($user->isModified()) {
			if(!$user->save()) {
				throw new \Exception("Could not save user");
			}
		}
		
		if($server->hasEmailAccount()) {
			$this->setEmailAccount($ldapUsername, $password, $record->mail[0], $server, $user);
		}
		
		return $user;
	
	}	
	
	private function createUser($username, Record $record) {
		$user = new User();
		$user->displayName = $record->cn[0];
		$user->username = $username;
		$user->email = $record->mail[0];
		$user->recoveryEmail = isset($record->mail[1]) ? $record->mail[1] : $record->mail[0];		
		
		if(!$user->save()) {
			throw new Exception("Could not save user after succesful IMAP login");
		}
		
		return $user;
	}
	
	
	private function setEmailAccount($username, $password, $email, Server $server, User $user) {
		
		if(!$user->hasModule('email')) {
			return;
		}
		
		//old framework code here		
		$accounts = Account::model()->findByAttributes(array(
					'host' => $server->imapHostname,
					'username' => $username
							))->fetchAll();
		
		$foundForUser = false;
		foreach($accounts as $account) {
			if($account->user_id == $user->id) {
				$foundForUser = true;
				break;
			}
		}
		
		if(!$foundForUser) {
			$account = new Account();
			$account->user_id = $user->id;
			$account->host = $server->imapHostname;
			$account->port = $server->imapPort;
			$account->username = $username;
			$account->password = $password;
			
			$account->imap_encryption = $server->imapEncryption;
			$account->imap_allow_self_signed = !$server->imapValidateCertificate;
			$account->smtp_allow_self_signed = !$server->imapValidateCertificate;
			$account->smtp_username = $server->smtpUsername;
			$account->smtp_password = $server->smtpPassword;
			$account->smtp_host = $server->smtpHostname;
			$account->smtp_port = $server->smtpPort;
			$account->smtp_encryption = $server->smtpEncryption;
			
			//$account->mbroot = ??
			
			$accounts = [$account];
			
		}
		
		foreach($accounts as $account) {
			$account->checkImapConnectionOnSave = false;
			
			$account->password = $password;			
			
			if($server->smtpUseUserCredentials) {				
				$account->smtp_username = $username;
				$account->smtp_password = $password;
			}
			
			$wasNew = $account->getIsNew();
			
			if(!$account->save(true)){
				throw new \Exception("Could not save e-mail account: ".implode("\n", $account->getValidationErrors()));				
			}
			
			if($wasNew) {
				$account->addAlias($email, $user->displayName);
			}
		}
		
	}

}
