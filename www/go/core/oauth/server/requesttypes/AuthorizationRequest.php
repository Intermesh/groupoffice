<?php

namespace go\core\oauth\server\requesttypes;

use League\OAuth2\Server\RequestTypes;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use OpenIDConnectServer\IdTokenResponse;

/**
 * Class AuthorizationRequest
 *
 * Extended to persist response_type parameter
 */
class AuthorizationRequest extends RequestTypes\AuthorizationRequest
{
    /**
     * Extend parent object
     *
     * @param RequestTypes\AuthorizationRequest $parent
     * @return AuthorizationRequest
     */
    static function extend(RequestTypes\AuthorizationRequest $parent)
    {
        $me = new self();
        $me->setGrantTypeId($parent->getGrantTypeId());
        $me->setClient($parent->getClient());
        $me->setRedirectUri($parent->getRedirectUri());
        $me->setState($parent->getState());
        $me->setScopes($parent->getScopes());
        $me->setCodeChallenge($parent->getCodeChallenge());
        $me->setCodeChallengeMethod($parent->getCodeChallengeMethod());

        return $me;
    }

    /**
     * @var array
     */
    protected $response_types;

    /**
     * Check for a given response_type
     *
     * @param string $type
     * @return bool
     */
    public function needResponseType($type)
    {
        return in_array($type, $this->response_types);
    }

    /**
     * @param string|array $response_types
     */
    public function setResponseTypes($response_types)
    {
        $this->response_types = is_array($response_types) ? $response_types : explode(' ', $response_types);
    }

    /**
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

    /**
     * @var IdTokenResponse
     */
    protected $response;

    /**
     * Set response object
     *
     * @param RedirectResponse $response
     */
    public function setResponse(IdTokenResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Get response object
     *
     * @return IdTokenResponse
     */
    public function getResponse()
    {
        return $this->response;
    }
}
