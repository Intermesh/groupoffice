<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\oauth\server\repositories;;

use DateTimeZone;
use go\core\db\Query;
use go\core\util\DateTime;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use go\core\model\OauthRefreshToken;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
		{
        $accessToken = $refreshTokenEntity->getAccessToken();

        // Forget the tokens that expired. This is cheap and means we don't need a cron job for it.
        go()->getDbConnection()->delete('core_oauth_refresh_token',
            (new Query())->where('expiryDateTime', '<', new DateTime("now", new DateTimeZone("UTC")))
        )->execute();

        go()->getDbConnection()->insert('core_oauth_refresh_token', [
            'identifier' => $refreshTokenEntity->getIdentifier(),
            'expiryDateTime' => \DateTimeImmutable::createFromInterface($refreshTokenEntity->getExpiryDateTime())
                ->setTimezone(new DateTimeZone("UTC"))->format('Y-m-d H:i:s'),
            'userIdentifier' => $accessToken->getUserIdentifier(),
            'clientId' => $accessToken->getClient()->id
        ])->execute();
    }

    /**
     * The server revokes a refresh token when it's used. It's replaced by a new one every time.
     *
     * {@inheritdoc}
     */
    public function revokeRefreshToken(string $tokenId): void
		{
        go()->getDbConnection()->delete('core_oauth_refresh_token', ['identifier' => $tokenId])->execute();
    }

    /**
     * A refresh token is only valid when it's in the database. So tokens that were never issued by this server, that
     * were used already or that were revoked are not accepted. Neither are the tokens of users that are disabled.
     *
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked(string $tokenId): bool
		{
        $userId = go()->getDbConnection()
            ->selectSingleValue('userIdentifier')
            ->from('core_oauth_refresh_token')
            ->where(['identifier' => $tokenId])
            ->single();

        return $userId === null || !UserRepository::isEnabled($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
		{
        return new OauthRefreshToken();
    }
}
