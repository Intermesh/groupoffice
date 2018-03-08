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

interface ClientConfigInterface
{
    public function setClientId($clientId);
    public function getClientId();
    public function setClientSecret($clientSecret);
    public function getClientSecret();
    public function setAuthorizeEndpoint($authorizeEndpoint);
    public function getAuthorizeEndpoint();
    public function setTokenEndpoint($tokenEndpoint);
    public function getTokenEndpoint();
    public function setRedirectUri($redirectUri);
    public function getRedirectUri();
    public function setCredentialsInRequestBody($credentialsInRequestBody);
    public function getCredentialsInRequestBody();
    public function setDefaultTokenType($defaultTokenType);
    public function getDefaultTokenType();
    public function setEnableDebug($enableDebug);
    public function getEnableDebug();
}
