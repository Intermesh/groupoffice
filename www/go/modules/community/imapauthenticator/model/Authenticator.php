<?php
namespace go\modules\community\imapauthenticator\model;

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
	 * @param string $username
	 * @return Server|boolean
	 */
	private static function findServer($username) {
		$adPos = strpos($username, '@');
		if(!$adPos) {
			return false;
		}
		
		$domain = substr($username, $adPos + 1);
		
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
			throw new \Exception("Could not connect to IMAP server");
		}
		
		if(!$connection->authenticate($username, $password)) {
			return false;
		}
		
		$user = \go\core\auth\model\User::find()->where(['username' => $username])->single();
		if($user) {
			return $user;
		}
		
		$user = new \go\core\auth\model\User();
		$user->displayName = $username;
		$user->username = $username;
		$user->email = $user->recoveryEmail = $username;
		
		if(!$user->save()) {
			throw new \Exception("Could not save user after succesful IMAP login");
		}
		
		return $user;
	}
	

}
