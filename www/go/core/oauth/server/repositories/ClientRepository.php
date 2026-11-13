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
use go\core\oauth\server\ClientRegistration;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity(string $clientIdentifier): ?\League\OAuth2\Server\Entities\ClientEntityInterface
		{
        $client = OauthClient::find()->where('identifier', '=',  $clientIdentifier)->single() ?: null;

        // Clients that registered themselves only work while a module offers a scope for them
        if ($client && $client->isSelfRegistered() && !ClientRegistration::isAllowed()) {
            return null;
        }

        // A client ID metadata document: the client ID is its URL
        if ((!$client || $client->getRegistration() === 'metadata') && ClientRegistration::isAllowed() && ClientRegistration::isMetadataUrl($clientIdentifier)) {
            $client = ClientRegistration::fromMetadataDocument($clientIdentifier, $client);
        }

        if(!$client) {
        	go()->debug("Could not get client '" . $clientIdentifier . "'");
        } else {
					go()->debug($client->toArray());
				}

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
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
