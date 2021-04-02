<?php

namespace go\core\oauth\server\grant;

use go\core\oauth\server\repositories\AuthCodeRepository;
use go\core\oauth\server\requesttypes\AuthorizationRequest;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant as OAuth2Grant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest as OAuth2AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use DateInterval;

class AuthCodeGrant extends OAuth2Grant\AuthCodeGrant
{
    use traits\GetClientTrait;

    /**
     * Reimplemented to set the nonce from the request
     *
     * As OpenID spec requires the nonce to be a claim in id_token.
     *
     * {@inheritdoc}
     */
    public function validateAuthorizationRequest(ServerRequestInterface $request)
    {
        $authorization_request = AuthorizationRequest::extend(
            parent::validateAuthorizationRequest($request));

        $authorization_request->setNonce(
            $this->getQueryStringParameter('nonce', $request));

        return $authorization_request;
    }

    /**
     * Nonce from authorization request
     *
     * @var string
     */
    private $nonce;

    /**
     * Reimplemented to get nonce from authorization request
     *
     * {@inheritdoc}
     */
    public function completeAuthorizationRequest(OAuth2AuthorizationRequest $authorizationRequest)
    {
        if ($authorizationRequest instanceof AuthorizationRequest) {
            $this->nonce = $authorizationRequest->getNonce();
        }

        $response = parent::completeAuthorizationRequest($authorizationRequest);
        unset($this->nonce);

        return $response;
    }

    /**
     * Issue an auth code.
     *
     * Reimplemented to set the nonce.
     *
     * @param DateInterval $authCodeTTL
     * @param ClientEntityInterface $client
     * @param string $userIdentifier
     * @param string|null $redirectUri
     * @param ScopeEntityInterface[] $scopes
     *
     * @return AuthCodeEntityInterface
     * @throws UniqueTokenIdentifierConstraintViolationException
     *
     * @throws OAuthServerException
     */
    protected function issueAuthCode(
        DateInterval $authCodeTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        $redirectUri,
        array $scopes = []
    )
    {
        $authCode = parent::issueAuthCode($authCodeTTL, $client, $userIdentifier, $redirectUri, $scopes);

        if (!empty($this->nonce) && $this->authCodeRepository instanceof AuthCodeRepository) {
            $this->authCodeRepository->setNonce($authCode, $this->nonce);
        }

        return $authCode;
    }

    /**
     * Respond to an access token request.
     *
     * @param ServerRequestInterface $request
     * @param ResponseTypeInterface $responseType
     * @param DateInterval $accessTokenTTL
     *
     * @return ResponseTypeInterface
     * @throws OAuthServerException
     *
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    )
    {
        $response = parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);

        // set nonce as claim in id_token response from stored auth-code
        $encryptedAuthCode = $this->getRequestParameter('code', $request, null);
        $authCodePayload = json_decode($this->decrypt($encryptedAuthCode));
        $response->setNonce($this->authCodeRepository->getNonce($authCodePayload->auth_code_id));

        return $response;
    }
}
