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

use fkooman\OAuth\Client\Exception\ClientConfigException;

class GoogleClientConfig extends ClientConfig implements ClientConfigInterface
{
    public function __construct(array $data)
    {
        // check if array is Google configuration object
        if (!isset($data['web'])) {
            throw new ClientConfigException("no configuration 'web' found, possibly wrong client type");
        }
        foreach (array('client_id', 'client_secret', 'auth_uri', 'token_uri', 'redirect_uris') as $key) {
            if (!isset($data['web'][$key])) {
                throw new ClientConfigException(sprintf("missing field '%s'", $key));
            }
        }

        // we map Google configuration to ClientConfig configuration
        $clientData = array(
            "client_id" => $data['web']['client_id'],
            "client_secret" => $data['web']['client_secret'],
            "authorize_endpoint" => $data['web']['auth_uri'],
            "token_endpoint" => $data['web']['token_uri'],
            "redirect_uri" => $data['web']['redirect_uris'][0],
            "credentials_in_request_body" => true
        );
        parent::__construct($clientData);
    }
}
