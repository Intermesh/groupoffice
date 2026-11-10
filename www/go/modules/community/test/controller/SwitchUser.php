<?php
namespace go\modules\community\test\controller;

use go\core\auth\TemporaryState;
use go\core\Controller;
use go\core\model\User;

/**
 * Authenticates like the WOPI controller: it replaces the auth state with a temporary state for another user. Used to
 * test that the JMAP router doesn't keep that state for the next methods in a request.
 */
class SwitchUser extends Controller
{
	protected function authenticate()
	{
		go()->setAuthState(new TemporaryState(User::ID_SUPER_ADMIN));
	}

	/**
	 * Returns the user ID the method runs as. It also reads the legacy user so it's copied into the legacy session.
	 *
	 * @return array
	 */
	public function whoAmI(): array
	{
		return ['userId' => go()->getUserId(), 'legacyUserId' => \GO::user()?->id];
	}

	/**
	 * Fails after authentication, like WOPI methods that aren't implemented.
	 *
	 * @throws \Exception
	 */
	public function fail(): void
	{
		throw new \Exception("Failed on purpose");
	}
}
