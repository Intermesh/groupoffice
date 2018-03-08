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

class GitHubClientConfig extends ClientConfig implements ClientConfigInterface
{
    public function __construct(array $data)
    {
        foreach (array('client_id', 'client_secret') as $key) {
            if (!isset($data[$key])) {
                throw new ClientConfigException(sprintf("missing field '%s'", $key));
            }
        }

        $clientData = array(
            "client_id" => $data['client_id'],
            "client_secret" => $data['client_secret'],
            "authorize_endpoint" => "https://github.com/login/oauth/authorize",
            "token_endpoint" => "https://github.com/login/oauth/access_token",
            "use_comma_separated_scope" => true,
            "credentials_in_request_body" => true
        );
        parent::__construct($clientData);
    }
}
