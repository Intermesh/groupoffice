<?php
/**
 * Based on the following MIT Licensed packages:
 * @link https://github.com/steverhoades/oauth2-openid-connect-server
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @link https://github.com/thephpleague/oauth2-server
 */

namespace go\core\oauth\server\grant\traits;

use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

/**
 * Trait to make protected method validateClient public as getClient
 */
trait GetClientTrait
{
    /**
     * Get the client from a request (like protected method validateClient)
     *
     * @param ServerRequestInterface $request
     * @throws OAuthServerException
     * @return ClientEntityInterface
     */
    public function getClient(ServerRequestInterface $request)
    {
		return $this->validateClient($request);
	}
}
