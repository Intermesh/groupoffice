<?php

namespace GO\Serverclient;


class ServerclientModule extends \GO\Base\Module{
	
	public static function initListeners() {
		
		\GO\Base\Model\User::model()->addListener("save", "GO\Serverclient\ServerclientModule", "saveUser");
		
		return parent::initListeners();
	}
	
	
	public static function getDomains(){
		return empty(\GO::config()->serverclient_domains) ? array() : array_map('trim',explode(",", \GO::config()->serverclient_domains));
	}
	
	public static function saveUser($user, $wasNew){
		
		if(!isset($_POST['serverclient_domains']))
			$_POST['serverclient_domains']=array();
		
		$domains = $wasNew ? $_POST['serverclient_domains'] : self::getDomains();
		
		if(!empty($domains)){

			$httpClient = new HttpClient();			

			foreach ($domains as $domain) {				
				if($wasNew){					
//					try{
//						$httpClient->postfixLogin();
//					}catch(\Exception $e){
//						throw new \Exception("Could not login to postfixadmin module. Check the username and password in /etc/groupoffice/globalconfig.inc.php\n\nMessage from Postfixadmin:\n\n".$e->getMessage());
//					}
					
					self::_addMailbox($httpClient,$user,$domain);
					self::_addAccount($user,$domain);
				}else
				{					
					if($user->unencryptedPassword){						
//						try{
//							$httpClient->postfixLogin();
//						}catch(\Exception $e){
//							throw new \Exception("Could not login to postfixadmin module. Check the username and password in /etc/groupoffice/globalconfig.inc.php\n\nMessage from Postfixadmin:\n\n".$e->getMessage());
//						}

						self::_setMailboxPassword($httpClient, $user,$domain);
					}
				}
			}
		}
	}
	
	private static function _addMailbox($httpClient, $user, $domain) {
		//strip domain from username if it's present.
		$username = str_replace('@'.$domain, '', $user->username);

		\GO::debug("SERVERCLIENT: Adding mailbox for " . $username . '@' . $domain);
		
		$alias = strpos($user->email,'@'.$domain) ? $user->email : '';

		//domain is, for example "intermesh .dev ".
		$url = "postfixadmin/mailbox/submit";
		$response = $httpClient->request($url, array(
				"r" => "postfixadmin/mailbox/submit",
				"name" => $user->name,
				"username" => $username,
				"alias"=>$alias,
				"password" => $user->getUnencryptedPassword(),
				"password2" => $user->getUnencryptedPassword(),
				"domain" => $domain
						));

		\GO::debug($response);

		$result = json_decode($response);

		if (!$result->success)
			throw new \Exception("Could not create mailbox on postfixadmin module. " . $result->feedback);
	}

	
	private static function _setMailboxPassword($httpClient, $user, $domain){
		//domain is, for example "intermesh.dev".
		
		\GO::debug("SERVERCLIENT: Updating password for mailbox ".$user->username.'@'.$domain);
		
		$username = $user->username;
		if(empty(\GO::config()->serverclient_dont_add_domain_to_imap_username))
			$username.='@'.$domain;
		
	
		$url = "postfixadmin/mailbox/submit";
		$response = $httpClient->request($url, array(
			"r"=>"postfixadmin/mailbox/setPassword",
			"username"=>$username,
			"password"=>$user->getUnencryptedPassword(),
		));
		
		\GO::debug($response);

		$result=json_decode($response);

		if(!$result->success)
			throw new \Exception("Could not set mailbox password on postfixadmin module. ".$result->feedback);
		
		if(\GO::modules()->isInstalled('email')){
			$stmt = \GO\Email\Model\Account::model()->findByAttributes(array(
					'username'=>$username
			));
			
			while($account = $stmt->fetch()){
				$account->password=$user->getUnencryptedPassword();
				$account->save(true);
			}
		}
	}
	
	private static function _addAccount($user,$domainName) {
		
		if(\GO::modules()->isInstalled('email')){
			
			\GO::debug("SERVERCLIENT: Adding e-mail account for ".$user->username.'@'.$domainName);
			
			$accountModel = new \GO\Email\Model\Account();
			$accountModel->user_id=$user->id;
			$accountModel->mbroot = \GO::config()->serverclient_mbroot;
			$accountModel->imap_encryption = !empty(\GO::config()->serverclient_use_ssl) ? 'ssl' : '';
			$accountModel->imap_encryption = !empty(\GO::config()->serverclient_use_tls) ? 'tls' : '';
			$accountModel->imap_allow_self_signed = \GO::config()->serverclient_novalidate_cert;
//			$accountModel->type=\GO::config()->serverclient_type;
			$accountModel->host=\GO::config()->serverclient_host;
			$accountModel->port=\GO::config()->serverclient_port;

//			$accountModel->name=$user->name;
			$accountModel->username=$user->username;
			if(empty(\GO::config()->serverclient_dont_add_domain_to_imap_username)){
				$accountModel->username.='@'.$domainName;
			}
			$accountModel->password=$user->getUnencryptedPassword();

			$accountModel->smtp_host=\GO::config()->serverclient_smtp_host;
			$accountModel->smtp_port=\GO::config()->serverclient_smtp_port;
			$accountModel->smtp_encryption=\GO::config()->serverclient_smtp_encryption;
			$accountModel->smtp_username=\GO::config()->serverclient_smtp_username;
			$accountModel->smtp_password=\GO::config()->serverclient_smtp_password;
			$accountModel->save();
			
			$alias = strpos($user->email, '@'.$domainName) ? $user->email : $accountModel->username;
			
			if(!strpos($alias, '@')){
				$alias .= '@'.$domainName;
			}
			
			
			$accountModel->addAlias($alias, $user->name);
		}
	}
}