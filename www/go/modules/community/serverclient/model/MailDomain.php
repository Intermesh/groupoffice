<?php

namespace go\modules\community\serverclient\model;

use Exception;
use GO\Email\Model\Account;

class MailDomain {
	
	private $http;
	private $password;
	
	public function __construct($password) {
		$this->password = $password;
		$this->http = new HttpClient();
	}
	
	public function addMailbox($user, $domain) {
		//strip domain from username if it's present.
		$username = str_replace('@'.$domain, '', $user->username);

		\GO::debug("SERVERCLIENT: Adding mailbox for " . $username . '@' . $domain);
		
		$alias = strpos($user->email,'@'.$domain) ? $user->email : '';

		//domain is, for example "intermesh .dev ".
		$response = $this->http->request("postfixadmin/mailbox/submit", array(
			"r" => "postfixadmin/mailbox/submit",
			"name" => $user->displayName,
			"username" => $username,
			"alias"=>$alias,
			"password" => $this->password,
			"password2" => $this->password,
			"domain" => $domain
		));

		\GO::debug($response);

		$result = json_decode($response);

		if (!$result->success)
			throw new Exception("Could not create mailbox on postfixadmin module. " . $result->feedback);
	}
	
	
	public function setMailboxPassword($user, $domain){
		
		\GO::debug("SERVERCLIENT: Updating password for mailbox ".$user->username.'@'.$domain);
		
		$username = $user->username;
		if(empty(\GO::config()->serverclient_dont_add_domain_to_imap_username))
			$username.='@'.$domain;
		
		$response = $this->http->request("postfixadmin/mailbox/submit", array(
			"r"=>"postfixadmin/mailbox/setPassword",
			"username"=>$username,
			"password"=>$this->password,
		));
		
		\GO::debug($response);

		$result=json_decode($response);

		if(!$result->success)
			throw new Exception("Could not set mailbox password on postfixadmin module. ".$result->feedback);
		
		if(!\GO::modules()->isInstalled('email')){
			return;
		}
		
		$stmt = Account::model()->findByAttributes(['username'=>$username]);

		while($account = $stmt->fetch()){
			$account->password=$this->password;
			$account->save(true);
		}
		
	}
	
	public function addAccount($user,$domain) {
		
		if(!\GO::modules()->isInstalled('email')){
			return;
		}
		
		\GO::debug("SERVERCLIENT: Adding e-mail account for ".$user->username.'@'.$domain);

		$account = new Account();
		$account->user_id = $user->id;
		$account->mbroot = \GO::config()->serverclient_mbroot;
		$account->imap_encryption = !empty(\GO::config()->serverclient_use_ssl) ? 'ssl' : '';
		$account->imap_encryption = !empty(\GO::config()->serverclient_use_tls) ? 'tls' : '';
		$account->imap_allow_self_signed = \GO::config()->serverclient_novalidate_cert ?? true;
		$account->host = \GO::config()->serverclient_host ?? "localhost";
		$account->port = \GO::config()->serverclient_port ?? 143;
		$account->username = $user->username;
		
		if(empty(\GO::config()->serverclient_dont_add_domain_to_imap_username)){
			$account->username .= '@'.$domain;
		}
		$account->password = $this->password;
		$account->smtp_host = \GO::config()->serverclient_smtp_host ?? 'localhost';
		$account->smtp_port = \GO::config()->serverclient_smtp_port ?? 25;
		$account->smtp_encryption = \GO::config()->serverclient_smtp_encryption;
		$account->smtp_username = \GO::config()->serverclient_smtp_username;
		$account->smtp_password = \GO::config()->serverclient_smtp_password;
		$account->save();

		$alias = strpos($user->email, '@'.$domain) ? $user->email : $account->username;

		if(!strpos($alias, '@')){
			$alias .= '@'.$domain;
		}

		$account->addAlias($alias, $user->displayName);
		
	}
	
}