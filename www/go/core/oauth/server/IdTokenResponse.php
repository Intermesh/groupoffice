<?php

namespace go\core\oauth\server;

use Lcobucci\JWT\Builder;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\IdTokenResponse as BaseIdTokenResponse;

/**
 * ID token response that signs the ID token with the issuer of this installation.
 *
 * The base class takes the issuer from the Host header and always prefixes "https://", so behind a proxy (internal
 * host name), on plain http or on a non-default port the "iss" claim does not match the issuer in the discovery
 * document and clients reject the token.
 */
class IdTokenResponse extends BaseIdTokenResponse
{
	/**
	 * The issuer: scheme, host and port of the configured Group-Office URL.
	 *
	 * The ID token "iss" claim and the "issuer" in the discovery document must be exactly this value.
	 */
	public static function issuer(): string
	{
		$url = parse_url(go()->getSettings()->URL);

		return ($url['scheme'] ?? 'https') . '://' . ($url['host'] ?? '') . (isset($url['port']) ? ':' . $url['port'] : '');
	}

	/**
	 * @return Builder
	 */
	protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity)
	{
		return parent::getBuilder($accessToken, $userEntity)->issuedBy(self::issuer());
	}
}
