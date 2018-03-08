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

class State extends Token
{
    /** state VARCHAR(255) NOT NULL */
    protected $state;

    public function __construct(array $data)
    {
        parent::__construct($data);

        foreach (array('state') as $key) {
            if (!array_key_exists($key, $data)) {
                throw new TokenException(sprintf("missing field '%s'", $key));
            }
        }

        $this->setState($data['state']);
    }

    public function setState($state)
    {
        if (!is_string($state) || 0 >= strlen($state)) {
            throw new TokenException("state needs to be a non-empty string");
        }
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }
}
