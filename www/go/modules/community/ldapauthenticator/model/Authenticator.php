<?php
namespace go\modules\community\ldapauthenticator\model;

use Exception;
use GO\Base\Mail\Exception\ImapAuthenticationFailedException;
use go\core\auth\PrimaryAuthenticator;
use go\core\ErrorHandler;
use go\core\ldap\Record;
use go\core\model\User;
use GO\Email\Model\Account;
use go\modules\community\ldapauthenticator\Module;
use go\modules\community\serverclient\model\MailDomain;

/**
 * LDAP Authenticator
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @author Merijn Schering <mschering@intermesh.nl> 
 */
class Authenticator extends PrimaryAuthenticator {
	
	public static function id() : string {
		return "ldap";
	}

	public static function isAvailableFor(string $username) : bool
	{
		
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
	 * Find a server by domain
	 *
	 * @param string $domain
	 * @return ?Server
	 */
	private static function findServer(string $domain): ?Server
	{
		return Server::find()
						->join('ldapauth_server_domain', 'd', 's.id = d.serverId')
						->where(['d.name' => $domain])
						//->orWhere(['d.name' => '*'])
						->single();
	}

	/**
	 * @throws Exception
	 *
	 */
	public function authenticate(string $username, string $password): bool|User
	{
		
		list($ldapUsername, $domain) = $this->splitUserName($username);

		$server = $this->findServer($domain);
		if($server->loginWithEmail) {
			$ldapUsername = $username;
		}
		
		$connection = $server->connect();

		$query = $server->getAuthenticationQuery($ldapUsername);
		
		$record = Record::find($connection, $server->peopleDN, $query)->fetch();

		if(!$record) {
			go()->debug("record not found");
			return false;
		}

		$dn = $record->getDn();

		go()->debug("Found record: " . $dn);
		
		if(!$connection->bind($dn, $password)) {
			go()->debug("Bind with password failed");
			return false;
		}

		$mappedValues = Module::mappedValues($record);

		if(empty($mappedValues['email'])) {
			throw new Exception("User '$username' has no 'e-mail' attribute set. Can't create a user");
		}
		
		$user = User::find()->where(['username' => $username])->orWhere('email', '=', $mappedValues['email'])->single();
		if(!$user) {
			$user = new User();
		}else if($user->hasPassword()){
			//password in database is not needed and clearing it improves security
			$user->clearPassword();
		}

		Module::ldapRecordToUser($username, $record, $user);
		
		foreach($server->groups as $group) {
			$user->addGroup($group->groupId);
		}
		if($user->isModified()) {
			if(!$user->save()) {
				throw new Exception("Could not save user: " . $user->getValidationErrorsAsString());
			}
		}
		
		if($server->hasEmailAccount()) {

			try {
				$this->setEmailAccount($domain, $ldapUsername, $password, $mappedValues['email'], $server, $user);
			} catch(ImapAuthenticationFailedException $e) {

				//ignore imap failure.
				ErrorHandler::logException($e);

			}
		}
		
		return $user;
	
	}


	private function addPostfixMailbox($domain, $username, $password, $email, Server $server, User $user) {
		if(!go()->getModule("community", "serverclient")) {
			return false;
		}

		$domains = \go\modules\community\serverclient\Module::getDomains();

		if(!in_array($domain, $domains)) {
			return false;
		}

		go()->debug("LDAPAUTH: Adding postfix mailbox for $username to $domain");

		$postfixAdmin = new MailDomain($password);

		try {
			$postfixAdmin->addMailbox($user, $domain);

			return true;
		} catch(Exception $e) {
			ErrorHandler::logException($e);
		}
		return false;
	}

	private function updatePostfixMailbox($domain, $username, $password, $email, Server $server, User $user) {
		if(!go()->getModule("community", "serverclient")) {
			return false;
		}

		$domains = \go\modules\community\serverclient\Module::getDomains();

		if(!in_array($domain, $domains)) {
			return false;
		}

		go()->debug("LDAPAUTH: Adding postfix mailbox for $username to $domain");

		$postfixAdmin = new MailDomain($password);

		try {
			$postfixAdmin->setMailboxPassword($user, $domain);

			return true;
		} catch(Exception $e) {
			ErrorHandler::logException($e);
		}
		return false;
	}

	/**
	 * @throws Exception
	 */
	private function setEmailAccount($domain, $username, $password, $email, Server $server, User $user) {
		
		if(!$user->hasModule('legacy', 'email')) {
			return;
		}

		$imapUsername = $server->imapUseEmailForUsername ? $email : $username;
		
		//old framework code here		
		$accounts = Account::model()->findByAttributes(array(
					'host' => $server->imapHostname,
					'username' => $imapUsername
							))->fetchAll();

		if(!$accounts) {
			$this->addPostfixMailbox($domain, $username, $password, $email, $server, $user);
		} else {
			$this->updatePostfixMailbox($domain, $username, $password, $email, $server, $user);
		}

		$foundForUser = false;
		foreach($accounts as $account) {
			/** @var Account $account */
			if($account->user_id == $user->id) {
				$foundForUser = true;
				break;
			}
		}
		
		if(!$foundForUser) {
			/** @noinspection DuplicatedCode */
			$account = new Account();
			$account->user_id = $user->id;
			$account->host = $server->imapHostname;
			$account->port = $server->imapPort;
			$account->username = $imapUsername;
			$account->password = $password;
			$account->imap_encryption = $server->imapEncryption ?? "";

			$account->imap_allow_self_signed = !$server->imapValidateCertificate;
			$account->smtp_allow_self_signed = !$server->smtpValidateCertificate;
			$account->smtp_username = $server->smtpUsername;
			$account->smtp_password = $server->smtpPassword;
			$account->smtp_host = $server->smtpHostname;
			$account->smtp_port = $server->smtpPort;
			$account->smtp_encryption = $server->smtpEncryption ?? "";
			
			//$account->mbroot = ??
			
			$accounts = [$account];
			
		}
		
		foreach($accounts as $account) {

			$account->password = $password;			
			
			if($server->smtpUseUserCredentials) {				
				$account->smtp_username = $imapUsername;
				$account->smtp_password = $password;
			}
			
			$wasNew = $account->getIsNew();
			$account->checkImapConnectionOnSave = $wasNew;
			
			if(!$account->save(true)){
				throw new Exception("Could not save e-mail account: ".implode("\n", $account->getValidationErrors()));
			}
			
			if($wasNew) {
				$account->addAlias($email, $user->displayName);
			}
		}
		
	}

}
