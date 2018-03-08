<?php
namespace go\core\cli;

use go\core\auth\model\User;
use go\core\auth\State as AbstractState;
use go\core\Environment;

class State extends AbstractState {
	
	private $userId;
	
	public function __construct($userId = User::ID_SUPER_ADMIN) {
		$this->userId = $userId;
	}
	
	public function getUser() {
		return User::findById($this->userId);
	}

	public function isAuthenticated() {
		return Environment::get()->isCli();
	}

}
