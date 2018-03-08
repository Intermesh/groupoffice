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

use fkooman\OAuth\Client\Exception\TokenResponseException;
use fkooman\OAuth\Common\Scope;

class TokenResponse
{
    private $accessToken;
    private $tokenType;
    private $expiresIn;
    private $refreshToken;
    private $scope;

    public function __construct(array $data)
    {
        foreach (array('access_token', 'token_type') as $key) {
            if (!array_key_exists($key, $data)) {
                throw new TokenResponseException(sprintf("missing field '%s'", $key));
            }
        }
        $this->setAccessToken($data['access_token']);
        $this->setTokenType($data['token_type']);

        $this->expiresIn = null;
        $this->refreshToken = null;
        $this->scope = null;
        if (array_key_exists('expires_in', $data)) {
            $this->setExpiresIn($data['expires_in']);
        }
        if (array_key_exists('refresh_token', $data)) {
            $this->setRefreshToken($data['refresh_token']);
        }
        if (array_key_exists('scope', $data)) {
            $this->setScope($data['scope']);
        }
    }

    public function setAccessToken($accessToken)
    {
        if (!is_string($accessToken) || 0 >= strlen($accessToken)) {
            throw new TokenResponseException("access_token needs to be a non-empty string");
        }
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setTokenType($tokenType)
    {
        if (!is_string($tokenType) || 0 >= strlen($tokenType)) {
            throw new TokenResponseException("token_type needs to be a non-empty string");
        }
        $this->tokenType = $tokenType;
    }

    public function getTokenType()
    {
        return $this->tokenType;
    }

    public function setExpiresIn($expiresIn)
    {
        if (!is_int($expiresIn) || 0 >= $expiresIn) {
            throw new TokenResponseException("expires_in needs to be a positive integer");
        }
        $this->expiresIn = $expiresIn;
    }

    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    public function setRefreshToken($refreshToken)
    {
        if (!is_string($refreshToken) || 0 >= strlen($refreshToken)) {
            throw new TokenResponseException("refresh_token needs to be a non-empty string");
        }
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setScope($scope)
    {
        $scope = Scope::fromString($scope);
        if ($scope->isEmpty()) {
            throw new TokenResponseException("scope must be non empty");
        }
        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }
}
