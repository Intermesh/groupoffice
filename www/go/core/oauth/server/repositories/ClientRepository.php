<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\oauth\server\repositories;;

use go\core\model\OauthClient;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier)
    {
        $client = OauthClient::find()->where('identifier', '=',  $clientIdentifier)->single();
        if(!$client) {
        	go()->debug("Could not get client '" . $clientIdentifier . "'");
        }

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $client = $this->getClientEntity($clientIdentifier);
        if(!$client) {
					return false;
        }

        if($client->isConfidential() && $client->checkSecret($clientSecret) === false)
        {
	        go()->debug("Invalid secret for '" . $clientIdentifier . "'");
        	return false;
        }

        return true;
    }
}
