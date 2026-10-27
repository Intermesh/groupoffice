<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\model;;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class OauthRefreshToken implements RefreshTokenEntityInterface
{
    use RefreshTokenTrait, EntityTrait;

    /**
     * Revoke everything the OAuth2 server issued to a user: refresh tokens and access tokens.
     *
     * Used when the user is disabled or changes the password. Clients have to authorize again after this.
     *
     * @param int|string $userId
     * @throws \Exception
     */
    public static function revokeAllForUser(int|string $userId): void
    {
        go()->getDbConnection()->delete('core_oauth_refresh_token', ['userIdentifier' => $userId])->execute();
        go()->getDbConnection()->delete('core_oauth_access_token', ['userIdentifier' => $userId])->execute();
        go()->getDbConnection()->delete('core_oauth_auth_codes', ['userIdentifier' => $userId])->execute();
    }
}
