<?php
namespace go\modules\community\imapauthenticator\model;

use Exception;
use go\core\auth\model\User;
use go\core\auth\PrimaryAuthenticator;
use go\core\imap\Connection;

class Authenticator extends PrimaryAuthenticator {
	
	public static function id() {
		return "imap";
	}

	public static function isAvailableFor($username) {		
		return static::findServer($username) != false;
	}
	
	/**
	 * 
	 * @param string $email
	 * @return Server|boolean
	 */
	private static function findServer($email) {
		$adPos = strpos($email, '@');
		if(!$adPos) {
			return false;
		}
		
		$domain = substr($email, $adPos + 1);
		
		return Server::find()
						->join('imapauth_server_domain', 'd', 's.id = d.serverId')
						->where(['d.name' => $domain])
						->orWhere(['d.name' => '*'])
						->single();
	}
	
	public function authenticate($username, $password) {
		$server = $this->findServer($username);
		
		$connection = new Connection();
		if(!$connection->connect($server->imapHostname, $server->imapPort, $server->imapEncryption == 'ssl')) {
			throw new Exception("Could not connect to IMAP server");
		}
		
		$imapUsername = $server->removeDomainFromUsername ? explode('@', $username)[0] : $username;
		
		if(!$connection->authenticate($imapUsername, $password)) {
			return false;
		}
		
		$user = User::find()->where(['username' => $username])->single();
		if(!$user) {
			$user = $this->createUser($username);
		}
		
		
		$this->setEmailAccount($imapUsername, $password, $username, $server, $user);
		
		return $user;
	
	}
	
	private function createUser($email) {
		$user = new User();
		$user->displayName = $email;
		$user->username = $email;
		$user->email = $user->recoveryEmail = $email;
		
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
		$accounts = \GO\Email\Model\Account::model()->findByAttributes(array(
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
			$account = new \GO\Email\Model\Account();
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
				$account->smtp_username = $imapUsername;
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
