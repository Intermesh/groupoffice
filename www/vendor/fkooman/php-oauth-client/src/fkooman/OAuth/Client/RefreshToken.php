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

use fkooman\OAuth\Client\Exception\TokenException;

class RefreshToken extends Token
{
    /** refresh_token VARCHAR(255) NOT NULL */
    private $refreshToken;

    public function __construct(array $data)
    {
        parent::__construct($data);

        foreach (array('refresh_token') as $key) {
            if (!array_key_exists($key, $data)) {
                throw new TokenException(sprintf("missing field '%s'", $key));
            }
        }

        $this->setRefreshToken($data['refresh_token']);
    }

    public function setRefreshToken($refreshToken)
    {
        if (!is_string($refreshToken) || 0 >= strlen($refreshToken)) {
            throw new TokenException("refresh_token needs to be a non-empty string");
        }
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }
}
