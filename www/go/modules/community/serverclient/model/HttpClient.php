<?php

namespace go\modules\community\serverclient\model;


class HttpClient extends \GO\Base\Util\HttpClient{
	
//	public function postfixLogin(){		
//		return $this->groupofficeLogin(\GO::config()->serverclient_server_url, \GO::config()->serverclient_username, \GO::config()->serverclient_password);		
//	}
	
	public function request($url, $params = array()) {
		
		
		if(empty(\GO::config()->serverclient_server_url)){
			\GO::config()->serverclient_server_url=\GO::config()->full_url;
		}
		
		$url = \GO::config()->serverclient_server_url.'?r='.$url;
		
		if(empty(\GO::config()->serverclient_token)){
			throw new \Exception("Could not connect to mailserver. Please set a strong password in /etc/groupoffice/globalconfig.inc.php.\n\nPlease remove serverclient_username and serverclient_password.\n\nPlease add:\n\n \$config['serverclient_token']='aStrongPasswordOfYourChoice';");
		}
		
		$params['serverclient_token']=\GO::config()->serverclient_token;	
		
		return parent::request($url, $params);
	}
	
//	public function postfixRequest($params){
//		$this->postfixLogin();		
//		
//		$url = \GO::config()->serverclient_server_url.'modules/postfixadmin/json.php';
//		
//		return $this->request($url, $params);
//	}	
	
	
}
