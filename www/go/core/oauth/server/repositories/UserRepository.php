<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\oauth\server\repositories;;

use go\core\model\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use go\core\model\OauthUser as UserEntity;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;

class UserRepository implements IdentityProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        // The password grant is not enabled on the server. Users log in on the authorization page.
        return;
    }

	/**
	 * @param $identifier
	 * @return bool|UserEntity
	 * @throws Exception
	 */
		public function getUserEntityByIdentifier($identifier): bool|UserEntity
		{
			$user = User::findById($identifier, ['username', 'displayName', 'id', 'email', 'modifiedAt', 'enabled']);
			if(!$user || !$user->enabled) {
				return false;
			}
			return new UserEntity($user);
		}

	/**
	 * Tokens and codes of a user who is disabled or deleted must not be accepted anymore.
	 *
	 * @param int|string|null $userId
	 */
	public static function isEnabled(int|string|null $userId): bool
	{
		if ($userId === null || $userId === '') {
			return false;
		}

		$user = User::find(['id', 'enabled'], true)->where(['id' => $userId])->single();

		return $user && $user->enabled;
	}
}
