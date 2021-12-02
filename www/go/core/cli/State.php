<?php
namespace go\core\cli;

use go\core\auth\TemporaryState;
use go\core\Environment;
use go\core\model\User;

class State extends TemporaryState {

	public function __construct($userId = User::ID_SUPER_ADMIN) {
		$this->setUserId($userId);
	}

	public function isAuthenticated(): bool
	{
		return parent::isAuthenticated();// && Environment::get()->isCli();
	}
}
