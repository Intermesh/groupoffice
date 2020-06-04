<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\model;

use go\core\orm\Entity;
use go\core\orm\Query;
use go\core\util\DateTime;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class OauthAccessToken extends Entity implements AccessTokenEntityInterface
{
    use AccessTokenTrait, TokenEntityTrait, EntityTrait;

    protected $clientId;

    protected static function defineMapping()
    {
	    return parent::defineMapping()
		    ->addTable('core_oauth_access_token');
    }

    public function setClient(ClientEntityInterface $client)
    {
	    $this->client = $client;
	    $this->clientId = $client->id;
    }

		public static function collectGarbage() {
			return static::delete((new Query)->where('expiryDateTime', '<', new DateTime()));
		}
}
