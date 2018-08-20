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

	public function setUser(User $user) {
		$this->user = $user;
	}
	
	public function getUser() {		
		return $this->user;
	}

	public function isAuthenticated() {
		return isset($this->user);
	}

	public function getUserId() {
		return isset($this->user) ? $this->user->id : null;
	}

}

