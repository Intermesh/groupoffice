<?php
namespace go\core\auth;

use go\core\auth\State as AbstractState;
use go\core\model\User;

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
	
	public function getUser(array $properties = []) {
		if(!empty($properties)) {
			return $this->user ?? User::findById($this->userId, $properties);
		}

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
		if(!isset(\GO::session()->values['user_id']) || \GO::session()->values['user_id'] != $userId) {
			\GO::session()->runAs($userId);
			//runas in old framework changes to user timezone.
			date_default_timezone_set("UTC");
		}
		
		return $this;
	}

	public function setUser(User $user) {
		$this->user = $user;
		return $this->setUserId($user->id);
	}

	/**
	 * Check if logged in user is admin
	 * 
	 * @return bool
	 */
	public function isAdmin() {
		if($this->userId == User::ID_SUPER_ADMIN) {
			return true;
		}

		$user = $this->getUser(['id']);
		if(!$user) {
			return false;
		}
		return $user->isAdmin();
	}
}

