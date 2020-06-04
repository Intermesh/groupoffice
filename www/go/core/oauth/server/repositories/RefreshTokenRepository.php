<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\oauth\server\repositories;;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use go\core\model\OauthRefreshToken;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        // Some logic to persist the refresh token in a database
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        // Some logic to revoke the refresh token in a database
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return false; // The refresh token has not been revoked
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new OauthRefreshToken();
    }
}
