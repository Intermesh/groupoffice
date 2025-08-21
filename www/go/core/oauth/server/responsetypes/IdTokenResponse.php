<?php

namespace go\core\oauth\server\responsetypes;

use DateTimeImmutable;
use go\core\oauth\server\AuthorizationServer;
use go\core\oauth\server\requesttypes\AuthorizationRequest;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;
use OpenIDConnectServer\IdTokenResponse as BaseIdTokenResponse;
use RuntimeException;

class IdTokenResponse extends BaseIdTokenResponse
{
    /**
     * Reimplement to:
     * - use X-Forwarded-Host header, if available, instead of Host
     *
     * Fixes JWT don't validate for client in certain proxying situations because of wrong issuer (eg. internal IP).
     *
     * @param AccessTokenEntityInterface $accessToken
     * @param UserEntityInterface $userEntity
     * @return Builder
     */
    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity)
    {
	    $claimsFormatter = ChainedFormatter::withUnixTimestampDates();
	    $builder = new Builder(new JoseEncoder(), $claimsFormatter);

	    $expiresAt = $accessToken->getExpiryDateTime();

	    // Add required id_token claims
	    return $builder
		    ->permittedFor($accessToken->getClient()->getIdentifier())
		    ->issuedBy(AuthorizationServer::getIssuer()) //issuer has to match
		    ->issuedAt(new DateTimeImmutable())
		    ->expiresAt($expiresAt)
		    ->relatedTo($userEntity->getIdentifier())
			->withClaim('nonce', $this->nonce) //nonce is supported by server
			->withHeader('kid', $this->kid); //kid has to match pub key defined in certs


    }

    /**
     * @var string
     */
    protected $nonce;

    /**
     * @var string
     */
    protected $kid;

    /**
     * Set nonce for id_token response, as it's required by OpenID Connect spec
     *
     * @param string $nonce
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @param $kid
     * @return void
     */
    public function setKid($kid)
    {
        $this->kid = $kid;
    }


    /**
     * Reimplemented to:
     * - make it public, to add id_token responses for implicit grant
     * - add nonce as claim as required by OpenID Connect spec
     *
     * @param AccessTokenEntityInterface $accessToken
     * @return array
     */
    public function getExtraParams(AccessTokenEntityInterface $accessToken, AuthorizationRequest|null $authorizationRequest = null)
    {
        if (false === $this->isOpenIDRequest($accessToken->getScopes())) {
            return [];
        }

        /** @var UserEntityInterface $userEntity */
        $userEntity = $this->identityProvider->getUserEntityByIdentifier($accessToken->getUserIdentifier());

        if (false === is_a($userEntity, UserEntityInterface::class)) {
            throw new RuntimeException('UserEntity must implement UserEntityInterface');
        }

        if (false === is_a($userEntity, ClaimSetInterface::class)) {
            throw new RuntimeException('UserEntity must implement ClaimSetInterface');
        }

        // Add required id_token claims
        $builder = $this->getBuilder($accessToken, $userEntity);

        // Need a claim factory here to reduce the number of claims by provided scope.
        $claims = $this->claimExtractor->extract($accessToken->getScopes(), $userEntity->getClaims());

		    foreach ($claims as $claimName => $claimValue) {
			    $builder = $builder->withClaim($claimName, $claimValue);
		    }

		    if (
			    method_exists($this->privateKey, 'getKeyContents')
			    && !empty($this->privateKey->getKeyContents())
		    ) {
			    $key = InMemory::plainText($this->privateKey->getKeyContents(), (string)$this->privateKey->getPassPhrase());
		    } else {
			    $key = LocalFileReference::file($this->privateKey->getKeyPath(), (string)$this->privateKey->getPassPhrase());
		    }

		    $token = $builder->getToken(new Sha256(), $key);

		    return [
			    'id_token' => $token->toString()
		    ];


    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    private function isOpenIDRequest($scopes)
    {
        // Verify scope and make sure openid exists.
        $valid = false;

        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() === 'openid') {
                $valid = true;
                break;
            }
        }

        return $valid;
    }
}