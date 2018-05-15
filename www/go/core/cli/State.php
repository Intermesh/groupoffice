<?php
namespace go\core\cli;

use go\core\auth\model\User;
use go\core\auth\State as AbstractState;
use go\core\Environment;

class State extends AbstractState {
	
	private $userId;
	private $user;


	public function __construct($userId = User::ID_SUPER_ADMIN) {
		$this->userId = $userId;
	}
	
	public function getUser() {
		if(!$this->user) {
			$this->user = User::findById($this->userId);
		}
		
		return $this->user;
	}

	public function isAuthenticated() {
		return Environment::get()->isCli();
	}

	public function getUserId() {
		return $this->userId;
	}

}
