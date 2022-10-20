<?php
namespace go\core\auth;

use go\core\auth\State as AbstractState;
use go\core\jmap\Request;
use go\core\model\User;

/**
 * TemporaryState class
 * 
 * Temporary state that will last for one script run.
 * It doesn't require a token in the database. It's used in *DAV modules that 
 * authenticate on each request.
 */
class TemporaryState extends AbstractState {

	public function __construct(int $userId = null)
	{
		if(isset($userId)) {
			$this->setUserId($userId);
		}
	}

	private $user;
	private $userId;	
	
	public function getUser(array $properties = []): ?User
	{
		if(!empty($properties)) {
			return $this->user ?? ($this->userId ? User::findById($this->userId, $properties) : null);
		}

		if(!$this->user) {
			$this->user =  $this->userId ? User::findById($this->userId) : null;
		}
		return $this->user;
	}

	public function isAuthenticated(): bool
	{
		return !empty($this->userId);
	}

	public function getUserId(): ?int
	{
		return $this->userId;
	}
	
	public function setUserId(?int $userId): TemporaryState
	{
		$this->userId = $userId;

		if(!empty(go()->getConfig()['debug_usernames'])) {
			$user = $this->getUser(['username']);
			if(in_array($user->username, go()->getConfig()['debug_usernames'])) {
				go()->getDebugger()->enable(true);
			}
		}

		return $this;
	}

	public function setUser(User $user): TemporaryState
	{
		$this->user = $user;
		return $this->setUserId($user->id);
	}

	/**
	 * Check if logged in user is admin
	 * 
	 * @return bool
	 */
	public function isAdmin(): bool
	{
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

