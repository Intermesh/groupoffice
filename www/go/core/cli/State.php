<?php
namespace go\core\cli;

use go\core\auth\TemporaryState;
use go\core\Environment;
use go\modules\core\users\model\User;

class State extends TemporaryState {

	public function __construct($userId = User::ID_SUPER_ADMIN) {
		$this->setUserId($userId);
	}

	public function isAuthenticated() {
		return parent::isAuthenticated();// && Environment::get()->isCli();
	}
}
