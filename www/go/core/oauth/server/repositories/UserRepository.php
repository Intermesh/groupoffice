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
        if ($username === 'alex' && $password === 'whisky') {
            return new User();
        }

        return;
    }

	/**
	 * @param $identifier
	 * @return bool|UserEntity
	 * @throws Exception
	 */
		public function getUserEntityByIdentifier($identifier)
		{
			$user = User::findById($identifier, ['username', 'displayName', 'id', 'email', 'modifiedAt']);
			if(!$user) {
				return false;
			}
			return new UserEntity($user);
		}
}
