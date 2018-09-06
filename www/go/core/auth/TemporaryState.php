<?php
namespace go\core\auth;

use go\core\auth\State as AbstractState;
use go\modules\core\users\model\User;

/**
 * TemporaryState class
 * 
 * Temporary state that will last for one script run.
 * It doesn't require a token in the database. It's used in *DAV modules that 
 * authenticate on each request.
 */
class TemporaryState extends AbstractState {
	
	private $user;
	private $userId;	
	
	public function getUser() {
		if(!$this->user) {
			$this->user = User::findById($this->userId);
		}
		
		return $this->user;
	}

	public function isAuthenticated() {
		return !empty($this->userId);
	}

	public function getUserId() {
		return $this->userId;
	}
	
	public function setUserId($userId) {
		$this->userId = $userId;
	}

	public function setUser(User $user) {
		$this->user = $user;
		$this->userId = $user->id;
	}
}

