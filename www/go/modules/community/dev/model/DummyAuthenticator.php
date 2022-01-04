<?php
namespace go\modules\community\dev\model;

use Exception;
use go\core\auth\SecondaryAuthenticator;
use go\core\model\Token;
use go\core\model\User;
use go\core\auth\PrimaryAuthenticator;
use go\core\imap\Connection;
use GO\Email\Model\Account;
use http\Exception\InvalidArgumentException;
use function GO;

class DummyAuthenticator extends SecondaryAuthenticator {

	public static function id()  : string{
		return "dummy";
	}

	public static function isAvailableFor(string $username) :bool{
		return true;
	}

	public function authenticate(Token $token, array $data) {
		if(!isset($data['code'])) {
			throw new \InvalidArgumentException("Code is required");
		}
		if($data['code'] == 'dummy') {
			return $token->getUser();
		} else{
			return false;
		}
	}

}
