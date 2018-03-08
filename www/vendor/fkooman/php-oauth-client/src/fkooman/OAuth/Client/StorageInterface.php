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

interface StorageInterface
{
    public function storeAccessToken(AccessToken $accessToken);
    public function getAccessToken($clientConfigId, Context $context);
    public function deleteAccessToken(AccessToken $accessToken);

    public function storeRefreshToken(RefreshToken $refreshToken);
    public function getRefreshToken($clientConfigId, Context $context);
    public function deleteRefreshToken(RefreshToken $refreshToken);

    public function storeState(State $state);
    public function getState($clientConfigId, $state);
    public function deleteState(State $state);
    public function deleteStateForContext($clientConfigId, Context $context);
}
