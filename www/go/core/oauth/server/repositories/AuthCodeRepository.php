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
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
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
    public function revokeAuthCode($codeId)
    {
        // Some logic to revoke the auth code in a database
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        return false; // The auth code has not been revoked
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new OauthAuthCode();
    }

    /**
     * Set nonce for an auth-code
     *
     * @param string|OauthAuthCode $codeId
     * @param string $nonce
     */
    public function setNonce($codeId, $nonce)
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
     */
    public function getNonce($codeId)
    {
        /** @var OauthAuthCode $oauthCode */
        $authCode = OauthAuthCode::find()->where(['identifier' => $codeId])->single();
        if (!$authCode) {
            return null;
        }

        return $authCode->getNonce();
    }
}
