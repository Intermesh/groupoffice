<?php

namespace go\core\oauth\server;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\IdTokenResponse as BaseIdTokenResponse;

/**
 * ID token response with the issuer from the configured URL instead of the "Host" header of the request
 *
 * Anyone can send any "Host" header, so it must not decide which issuer the server announces and signs.
 */
class IdTokenResponse extends BaseIdTokenResponse
{
	/**
	 * The issuer of this server: the scheme, host and port of the URL in the system settings
	 *
	 * Without a path, like it was when it came from the "Host" header, so clients keep working. The scheme must
	 * be the real one, because MCP clients fetch the server metadata from the issuer (RFC 9728), so an https issuer
	 * on an http installation can't be reached.
	 *
	 * @return string
	 */
	public static function issuer(): string
	{
		$url = parse_url(go()->getSettings()->URL ?? '');
		if (empty($url['host'])) {
			// Not configured. Only happens before the installation is finished.
			return 'https://' . ($_SERVER['HTTP_HOST'] ?? '');
		}
		$scheme = strtolower($url['scheme'] ?? '') === 'http' ? 'http' : 'https';
		$issuer = $scheme . '://' . $url['host'];
		if (isset($url['port'])) {
			$issuer .= ':' . $url['port'];
		}
		return $issuer;
	}

	protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity)
	{
		return parent::getBuilder($accessToken, $userEntity)->issuedBy(self::issuer());
	}
}
