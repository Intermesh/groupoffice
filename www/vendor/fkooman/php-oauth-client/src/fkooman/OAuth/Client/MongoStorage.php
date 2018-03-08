<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace fkooman\OAuth\Client;

use fkooman\OAuth\Common\Scope;

/**
 * Class MongoStorage
 *
 *  This is an implementation of the StorageInterface of the fkooman client for MongoDB storage.
 *
 */
class MongoStorage implements \fkooman\OAuth\Client\StorageInterface
{
    /** @var MongoClient */
    private $mongo;

    private $db;

    public function __construct(MongoClient $mongo, $db)
    {
        $this->mongo = $mongo;
        $this->db = $db;
    }

    public function getAccessToken($clientConfigId, fkooman\OAuth\Client\Context $context)
    {
        $collection = $this->mongo->selectCollection($this->db, 'access_tokens');
        $scope = $context->getScope()->isEmpty() ? null : $context->getScope()->toString();
        $uid = $context->getUserId();
        $result = $collection->findOne(
            array(
                'user_id' => $uid,
                'client_config_id' => $clientConfigId,
                'scope' => $scope
            )
        );

        if (null !== $result) {
            $result['scope'] = Scope::fromString($result['scope']);

            return new fkooman\OAuth\Client\AccessToken($result);
        }

        return false;
    }

    public function storeAccessToken(fkooman\OAuth\Client\AccessToken $accessToken)
    {
        $collection = $this->mongo->selectCollection($this->db, 'access_tokens');
        $result = $collection->insert(
            array(
                'client_config_id' => $accessToken->getClientConfigId(),
                'user_id'          => $accessToken->getUserId(),
                'scope'            => $accessToken->getScope()->isEmpty() ? null : $accessToken->getScope()->toString(),
                'access_token'     => $accessToken->getAccessToken(),
                'token_type'       => $accessToken->getTokenType(),
                'expires_in'       => $accessToken->getExpiresIn(),
                'issue_time'       => $accessToken->getIssueTime()
            )
        );

        return (is_array($result) && $result['ok'] == 1) || $result;
    }

    public function deleteAccessToken(fkooman\OAuth\Client\AccessToken $accessToken)
    {
        $collection = $this->mongo->selectCollection($this->db, 'access_tokens');
        $result = $collection->remove(
            array(
                'client_config_id' => $accessToken->getClientConfigId(),
                'user_id'          => $accessToken->getUserId(),
                'access_token'     => $accessToken->getAccessToken()
            )
        );

        return (is_array($result) && $result['ok'] == 1 && $result['n'] > 0) || $result;
    }

    public function getRefreshToken($clientConfigId, fkooman\OAuth\Client\Context $context)
    {
        $collection = $this->mongo->selectCollection($this->db, 'refresh_tokens');
        $scope = $context->getScope()->isEmpty() ? null : $context->getScope()->toString();
        $uid = $context->getUserId();
        $result = $collection->findOne(
            array(
                'user_id' => $uid,
                'client_config_id' => $clientConfigId,
                'scope' => $scope
            )
        );

        if (null !== $result) {
            $result['scope'] = Scope::fromString($result['scope']);

            return new fkooman\OAuth\Client\RefreshToken($result);
        }

        return false;
    }

    public function storeRefreshToken(fkooman\OAuth\Client\RefreshToken $refreshToken)
    {
        $collection = $this->mongo->selectCollection($this->db, 'refresh_tokens');
        $result = $collection->insert(
            array(
                'client_config_id' => $refreshToken->getClientConfigId(),
                'user_id'          => $refreshToken->getUserId(),
                'scope'            => $refreshToken->getScope()->isEmpty() ? null : $refreshToken->getScope()->toString(),
                'refresh_token'    => $refreshToken->getRefreshToken(),
                'issue_time'       => $refreshToken->getIssueTime()
            )
        );

        return (is_array($result) && $result['ok'] == 1) || $result;
    }

    public function deleteRefreshToken(fkooman\OAuth\Client\RefreshToken $refreshToken)
    {
        $collection = $this->mongo->selectCollection($this->db, 'refresh_tokens');
        $result = $collection->remove(
            array(
                'client_config_id' => $refreshToken->getClientConfigId(),
                'user_id'          => $refreshToken->getUserId(),
                'refresh_token'     => $refreshToken->getRefreshToken()
            )
        );

        return (is_array($result) && $result['ok'] == 1 && $result['n'] > 0) || $result;
    }

    public function getState($clientConfigId, $state)
    {
        $collection = $this->mongo->selectCollection($this->db, 'state');
        $result = $collection->findOne(
            array(
                'client_config_id' => $clientConfigId,
                'state' => $state
            )
        );

        if (null !== $result) {
            $result['scope'] = Scope::fromString($result['scope']);

            return new fkooman\OAuth\Client\State($result);
        }

        return false;
    }

    public function storeState(fkooman\OAuth\Client\State $state)
    {
        $collection = $this->mongo->selectCollection($this->db, 'state');
        $result = $collection->insert(
            array(
                'client_config_id' => $state->getClientConfigId(),
                'user_id'          => $state->getUserId(),
                'scope'            => $state->getScope()->isEmpty() ? null : $state->getScope()->toString(),
                'state'            => $state->getState(),
                'issue_time'       => $state->getIssueTime()
            )
        );

        return (is_array($result) && $result['ok'] == 1) || $result;
    }

    public function deleteStateForContext($clientConfigId, fkooman\OAuth\Client\Context $context)
    {
        $collection = $this->mongo->selectCollection($this->db, 'state');
        $result = $collection->remove(
            array(
                'client_config_id' => $clientConfigId,
                'user_id' => $context->getUserId()
            )
        );

        return (is_array($result) && $result['ok'] == 1 && $result['n'] > 0) || $result;
    }

    public function deleteState(fkooman\OAuth\Client\State $state)
    {
        $collection = $this->mongo->selectCollection($this->db, 'state');
        $result = $collection->remove(
            array(
                'client_config_id' => $state->getClientConfigId(),
                'state' => $state->getState()
            )
        );

        return (is_array($result) && $result['ok'] == 1 && $result['n'] > 0) || $result;
    }
}
