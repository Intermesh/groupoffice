<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\oauth\server\repositories;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use go\core\model\OauthAuthCode;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
		{
        $oauthCode = new OauthAuthCode();
        $oauthCode->setClientId($authCodeEntity->getClient()->id);
        $oauthCode->setIdentifier($authCodeEntity->getIdentifier());
        $oauthCode->setUserIdentifier($authCodeEntity->getUserIdentifier());
        $oauthCode->setExpiryDateTime($authCodeEntity->getExpiryDateTime());
        //additional attributes like created, ip....
        $oauthCode->save();

        $authCodeEntity->id = $oauthCode->id;

        //store scopes if necessary
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode(string $codeId): void
		{
        go()->getDbConnection()->delete('core_oauth_auth_codes', ['identifier' => $codeId])->execute();
    }

    /**
     * An authorization code can be used once. A code that is not in the database is revoked: it was already used, it
     * expired or it was never issued by this server.
     *
     * The server calls this when a code is exchanged for tokens. The code is consumed here by deleting it, which is
     * atomic. If two requests exchange the same code at the same time only one of them succeeds.
     *
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked(string $codeId): bool
		{
        $userId = go()->getDbConnection()
            ->selectSingleValue('userIdentifier')
            ->from('core_oauth_auth_codes')
            ->where(['identifier' => $codeId])
            ->single();

        if($userId === null) {
            return true;
        }

        $delete = go()->getDbConnection()->delete('core_oauth_auth_codes', ['identifier' => $codeId]);
        $delete->execute();
        if($delete->rowCount() !== 1) {
            // somebody else used it in the meantime
            return true;
        }

        return !UserRepository::isEnabled($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
		{
        return new OauthAuthCode();
    }

    /**
     * Set nonce for an auth-code
     *
     * @param string|OauthAuthCode $codeId
     * @param string $nonce
     */
    public function setNonce(string|OauthAuthCode $codeId, string $nonce): void
		{
        if ($codeId instanceof OauthAuthCode) {
            $codeId = $codeId->getIdentifier();
        }

        $authCode = OauthAuthCode::find()->where(['identifier' => $codeId])->single();
        $authCode->setNonce($nonce);
        $authCode->save();
    }

	/**
	 * Get nonce of an auth-code
	 *
	 * @param string $codeId
	 *
	 * @return string|null nonce of authorization request
	 * @throws \Exception
	 */
    public function getNonce(string $codeId): ?string
		{
        /** @var OauthAuthCode $oauthCode */
        $authCode = OauthAuthCode::find()->where(['identifier' => $codeId])->single();
        if (!$authCode) {
            return null;
        }

        return $authCode->getNonce();
    }
}
