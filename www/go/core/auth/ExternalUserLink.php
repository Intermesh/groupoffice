<?php

namespace go\core\auth;

use Exception;
use go\core\db\Criteria;
use go\core\exception\Forbidden;
use go\core\model\User;
use go\core\orm\exception\SaveException;

/**
 * Finds the Group-Office user of a login with an external identity provider (OpenID Connect, OAuth2)
 *
 * A user is linked to the subject ("sub" claim) of a client of the provider in a link table with the columns userId,
 * clientId and sub. The e-mail address is only used to make that link the first time:
 *
 * 1. A user that is linked to the subject of this client logs in.
 * 2. A link to this client that has no subject yet, made by an older version or by an administrator, gets the subject
 *    when the e-mail address of the user matches.
 * 3. An existing user with the e-mail address is only linked when the provider says that the address is verified,
 *    and the user is not an administrator, has no password in Group-Office and isn't linked to another subject.
 *    Otherwise anyone who can choose the e-mail address at the provider could log in as that user.
 * 4. Otherwise a new user is created.
 */
class ExternalUserLink
{
	/**
	 * @param string $table The link table of the module, eg. "oidc_user"
	 * @param int $clientId The ID of the client of the provider in Group-Office
	 */
	public function __construct(private readonly string $table, private readonly int $clientId)
	{
	}

	/**
	 * Find or create the user of the claims of the provider and link it to the subject
	 *
	 * @param array $claims The user info claims: sub, email, email_verified and name
	 * @return User
	 * @throws Forbidden When the user can't be linked
	 * @throws Exception
	 */
	public function findOrCreateUser(array $claims): User
	{
		$sub = $claims['sub'] ?? null;
		// OpenID Connect: at most 255 ASCII characters
		if (!is_string($sub) || !preg_match('/^[\x20-\x7E]{1,255}$/', $sub)) {
			throw new Forbidden(go()->t("The identity provider didn't send a valid user ID (sub)"));
		}

		$userId = go()->getDbConnection()
			->selectSingleValue('userId')
			->from($this->table)
			->where(['clientId' => $this->clientId, 'sub' => $sub])
			->single();

		if ($userId) {
			$user = User::findById($userId);
			if ($user) {
				return $user;
			}
		}

		$email = $claims['email'] ?? null;
		if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new Forbidden(go()->t("Could not get a valid e-mail address from the identity provider"));
		}

		if ($this->isExplicitlyUnverified($claims)) {
			throw new Forbidden(go()->t("The e-mail address is not verified by the identity provider"));
		}

		$user = $this->findUserLinkedWithoutSub($email);
		if (!$user) {
			$user = $this->findUserToLink($email, $claims);
		}
		if (!$user) {
			$user = $this->createUser($email, $claims);
		}

		$this->link($user, $sub);

		return $user;
	}

	/**
	 * A link without subject was made by an older version that linked by e-mail address, or by an administrator.
	 */
	private function findUserLinkedWithoutSub(string $email): ?User
	{
		$userId = go()->getDbConnection()
			->selectSingleValue('l.userId')
			->from($this->table, 'l')
			->join('core_user', 'u', 'u.id = l.userId')
			->where(['l.clientId' => $this->clientId])
			->andWhere('l.sub', 'IS', null)
			->andWhere((new Criteria())->where('u.email', '=', $email)->orWhere('u.username', '=', $email))
			->single();

		return $userId ? User::findById($userId) : null;
	}

	private function isExplicitlyUnverified(array $claims): bool
	{
		return isset($claims['email_verified']) && !filter_var($claims['email_verified'], FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Find an existing user with the e-mail address that may be linked automatically
	 *
	 * @throws Forbidden
	 */
	private function findUserToLink(string $email, array $claims): ?User
	{
		$users = User::find()
			->where(['username' => $email])
			->orWhere(['email' => $email])
			->all();

		if (empty($users)) {
			return null;
		}

		$verified = isset($claims['email_verified']) && filter_var($claims['email_verified'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;

		// A user that is already linked to another subject is never linked to this one by e-mail address
		$linked = count($users) == 1 && go()->getDbConnection()
			->selectSingleValue('userId')
			->from($this->table)
			->where(['userId' => $users[0]->id])
			->single();

		if (count($users) > 1 || !$verified || $linked || $users[0]->isAdmin() || $users[0]->hasPassword()) {
			go()->debug("Not linking existing user with e-mail address " . $email . " to client " . $this->clientId . " of " . $this->table);
			throw new Forbidden(go()->t("There's already an account with this e-mail address. Ask your administrator to link it to this login."));
		}

		return $users[0];
	}

	/**
	 * @throws SaveException
	 * @throws Forbidden
	 */
	private function createUser(string $email, array $claims): User
	{
		if (!go()->getSettings()->allowRegistration) {
			throw new Forbidden(go()->t("User not found and registration not allowed"));
		}

		$name = isset($claims['name']) && is_string($claims['name']) && $claims['name'] !== '' ? $claims['name'] : $email;

		$user = new User();
		$user->username = $email;
		$user->email = $email;
		$user->recoveryEmail = $email;
		$user->displayName = $name;
		if (!$user->save()) {
			throw new SaveException($user);
		}

		return $user;
	}

	private function link(User $user, string $sub): void
	{
		$db = go()->getDbConnection();
		$updated = $db->update($this->table, ['sub' => $sub], ['userId' => $user->id, 'clientId' => $this->clientId, 'sub' => null]);
		$updated->execute();
		if ($updated->rowCount() > 0) {
			return;
		}

		$db->insertIgnore($this->table, ['userId' => $user->id, 'clientId' => $this->clientId, 'sub' => $sub])->execute();
	}
}
