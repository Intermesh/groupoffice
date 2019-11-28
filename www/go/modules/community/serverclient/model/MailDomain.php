<?php

namespace go\modules\community\serverclient\model;

use Exception;
use GO;
use go\core\http\Client;
use GO\Email\Model\Account;

class MailDomain {
	
	private $http;
	private $password;
	
	public function __construct($password) {
		$this->password = $password;
		$this->http = new Client();
	}

	private function getBaseUrl($url) {
		if(empty(GO::config()->serverclient_server_url)){
			GO::config()->serverclient_server_url= GO::config()->full_url;
		}

		if(empty(GO::config()->serverclient_token)){
			throw new Exception("Could not connect to mailserver. Please set a strong password in /etc/groupoffice/globalconfig.inc.php.\n\nPlease remove serverclient_username and serverclient_password.\n\nPlease add:\n\n \$config['serverclient_token']='aStrongPasswordOfYourChoice';");
		}

		$url = GO::config()->serverclient_server_url.'?r='.$url.'&serverclient_token='. GO::config()->serverclient_token;

		return $url;
	}
	
	public function addMailbox($user, $domain) {
		//strip domain from username if it's present.
		$username = str_replace('@'.$domain, '', $user->username);

		
		$alias = strpos($user->email,'@'.$domain) ? $user->email : '';

		$url = $this->getBaseUrl("postfixadmin/mailbox/submit");
		$params = array(
			"name" => $user->displayName,
			"username" => $username,
			"alias"=>$alias,
			"password" => $this->password,
			"password2" => $this->password,
			"domain" => $domain
		);

		go()->debug($url);
		go()->debug($params);
		//domain is, for example "intermesh .dev ".
		$response = $this->http->post($url, $params);

		go()->debug($response);

		if($response['status'] != 200) {
			throw new Exception("Unexpected HTTP status " .$response['status'] ." from ". $url);
		}
		$result = json_decode($response['body']);	

		if(!$result) {
			throw new Exception("Could not create mailbox on postfixadmin module. " . $response);
		}

		if (!$result->success)
			throw new Exception("Could not create mailbox on postfixadmin module. " . $result->feedback);
	}
	
	
	public function setMailboxPassword($user, $domain){
		
		go()->debug("SERVERCLIENT: Updating password for mailbox ".$user->username.'@'.$domain);
		
		$username = $user->username;
		if(empty(GO::config()->serverclient_dont_add_domain_to_imap_username))
			$username.='@'.$domain;
		
		$url = $this->getBaseUrl("postfixadmin/mailbox/setPassword");

		$params = array(			
			"username"=>$username,
			"password"=>$this->password,
		);

		go()->debug($url);
		go()->debug($params);

		$response = $this->http->post($url, $params);

		go()->debug($response);
		
		if($response['status'] != 200) {
			throw new Exception("Unexpected HTTP status " .$response['status'] ." from ". $url);
		}

		$result = json_decode($response['body']);

		if(!$result) {
			throw new Exception("Could not create mailbox on postfixadmin module. " . $response);
		}

		if(!$result->success)
			throw new Exception("Could not set mailbox password on postfixadmin module. ".$result->feedback);
		
		if(!GO::modules()->isInstalled('email')){
			return;
		}
		
		$stmt = Account::model()->findByAttributes(['username'=>$username]);

		while($account = $stmt->fetch()){
			$account->password=$this->password;
			$account->save(true);
		}
		
	}
	
	public function addAccount($user,$domain) {
		
		if(!GO::modules()->isInstalled('email')){
			return;
		}
		
		go()->debug("SERVERCLIENT: Adding e-mail account for ".$user->username.'@'.$domain);

		$account = new Account();
		$account->user_id = $user->id;
		$account->mbroot = GO::config()->serverclient_mbroot;
        $account->imap_encryption = '';

        if (!empty(GO::config()->serverclient_use_ssl))
            $account->imap_encryption = 'ssl';

        if (!empty(GO::config()->serverclient_use_tls))
            $account->imap_encryption = 'tls';

		$account->imap_allow_self_signed = GO::config()->serverclient_novalidate_cert ?? true;
		$account->host = GO::config()->serverclient_host ?? "localhost";
		$account->port = GO::config()->serverclient_port ?? 143;
		$account->username = $user->username;
		
		if(empty(GO::config()->serverclient_dont_add_domain_to_imap_username)){
			$account->username .= '@'.$domain;
		}
		$account->password = $this->password;
		$account->smtp_host = GO::config()->serverclient_smtp_host ?? 'localhost';
		$account->smtp_port = GO::config()->serverclient_smtp_port ?? 25;
		$account->smtp_encryption = GO::config()->serverclient_smtp_encryption;
		$account->smtp_username = GO::config()->serverclient_smtp_username;
		$account->smtp_password = GO::config()->serverclient_smtp_password;
		$account->save();

		$alias = strpos($user->email, '@'.$domain) ? $user->email : $account->username;

		if(!strpos($alias, '@')){
			$alias .= '@'.$domain;
		}

		$account->addAlias($alias, $user->displayName);
		
	}
	
}