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
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class OauthAuthCode extends Entity implements AuthCodeEntityInterface
{
    use EntityTrait, TokenEntityTrait, AuthCodeTrait;

    public $id;

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping()
    {
        return parent::defineMapping()
            ->addTable('core_oauth_auth_codes');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function collectGarbage()
    {
        return static::delete((new Query)->where('expiryDateTime', '<', new DateTime()));
    }

    /**
     * Nonce from authorization request
     *
     * @var string
     */
    protected $nonce;

    /**
     * @param string $nonce
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @return string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    protected $clientId;

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    public function getClientId()
    {
        return $this->clientId;
    }
}
