<?php

namespace go\modules\community\oauth2client\model;

use go\core\exception\NotFound;
use go\core\http\Exception;
use go\core\jmap\Entity;
use go\core\orm\Mapping;
use go\core\validate\ErrorCode;
use go\modules\community\email\model\Account;
use go\modules\community\oauth2client\provider\Azure;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Google;

final class Oauth2Client extends Entity
{
	public ?string $id;
	public string $name;
	public ?string $defaultClientId;
	protected string $clientSecret;
	public string $clientId;
	public ?string $projectId;

	/**
	 * The secret is write only. It's never sent to a client because everybody who can read this entity (the controller
	 * is accessible without logging in for the login page) could use it to impersonate the application.
	 */
	public function getClientSecret(): ?string
	{
		return null;
	}

	/**
	 * As the secret is never returned, a client can't send it back unchanged. So an empty value keeps the current secret.
	 */
	public function setClientSecret(string $secret): void
	{
		if ($secret === '' && !$this->isNew()) {
			return;
		}

		$this->clientSecret = $secret;
	}

	protected function internalValidate()
	{
		if ($this->isNew() && empty($this->clientSecret)) {
			$this->setValidationError('clientSecret', ErrorCode::REQUIRED);
		}

		parent::internalValidate();
	}


	/**
	 * If true a login with {$name} button will appear on the login screen to use openid for login
	 *
	 * @var bool
	 */
	public bool $openId = false;


	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("oauth2client_oauth2client");
	}


	/**
	 * Mini factory for OAuth2 client providers
	 *
	 * @return ?AbstractProvider
	 * @throws NotFound
	 */
	public function getProvider(array|null $scopes = null): ?AbstractProvider
	{
		$defaultClient = DefaultClient::findById($this->defaultClientId);
		$url = rtrim(go()->getSettings()->URL, '/');
		$prvVendorName = 'League';
		$params = [
			'clientId' => $this->clientId,
			'clientSecret' => $this->clientSecret,
			'redirectUri' => $url . '/go/modules/community/oauth2client/gauth.php/callback',
		];
		switch ($defaultClient->name) {

			case 'Google':
				// see https://developers.google.com/identity/protocols/oauth2/web-server
				$params['accessType'] = 'offline';
				$params['scopes'] = $scopes ?? ['openid', 'profile', 'email','https://mail.google.com/'];

				return new Google($params);

			case 'Azure':
				// https://docs.microsoft.com/en-us/exchange/client-developer/legacy-protocols/how-to-authenticate-an-imap-pop-smtp-application-by-using-oauth
				// https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-auth-code-flow

				// unfortunately the "profile" scope can't be mixed with the outlook scopes :(
				// https://stackoverflow.com/questions/61597263/office-365-xoauth2-for-imap-and-smtp-authentication-fails/61678485#61678485
				// so we need two tokens
				$params['tenant'] = $this->projectId;
				$params['scopes'] = $scopes ?? [
					'openid',
					'offline_access',
					'email',
					'https://outlook.office365.com/IMAP.AccessAsUser.All',
					'https://outlook.office365.com/SMTP.Send'
				];
				$params['defaultEndPointVersion'] = '2.0';

				return new Azure($params, [
					// Use PHP's stream handler instead of libcurl. Microsoft returns an empty 404 for some libcurl/OpenSSL
					// builds (curl 8.14 + OpenSSL 3.5) while wget and PHP streams work fine. This also avoids the old
					// HTTP/2 + ALPN 404 bug that curl's CURLOPT_SSL_ENABLE_ALPN workaround targeted, which newer
					// Guzzle versions no longer allow in the "curl" option.
					'httpClient' => new \GuzzleHttp\Client([
						'handler' => \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\StreamHandler())
					])
				]);

			default:
				throw new NotFound('Default client ' . $defaultClient->name . ' not supported');
		}

	}

	/**
	 * @param array $tokenParams
	 * @param mixed $provider
	 * @return mixed
	 * @throws NotFound
	 */
	private function getAccessTokenCls(array $tokenParams, $provider)
	{
		$defaultClient = DefaultClient::findById($this->defaultClientId);
		$prvVendorName = ($defaultClient->name === 'Azure') ? 'TheNetworg' : 'League';
		$clsName = $prvVendorName . "\\OAuth2\\Client\\Token\\AccessToken";
		return new $clsName($tokenParams, $provider);
	}

	/**
	 * Checks whether the access token is still valid. If not, renew it using refresh token
	 *
	 * The token parameter expires_in is preferred over the 'expires' parameter and is calculated as the
	 * seconds (positive or negative) since the current time.
	 *
	 * @param Account $account
	 * @param array $tokenParams ['refresh_token','access_token','expires_in']
	 * @throws \Exception
	 * @throws NotFound
	 */
	public function maybeRefreshAccessToken(Account $account, array $tokenParams)
	{
		$provider = $this->getProvider();

		try {
			$currentAccessToken = $this->getAccessTokenCls($tokenParams, $provider);
			if ($currentAccessToken->hasExpired()) {
				$newAccessToken = $provider->getAccessToken('refresh_token', [
					'refresh_token' => $tokenParams['refresh_token']
				]);
				$account->oauth2_account->token = $newAccessToken->getToken();
				$account->oauth2_account->expires = $newAccessToken->getExpires();
				if (!$account->save()) {
					throw new Exception(500, "Unable to refresh access token");
				}
			}
		}

		catch(\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
			// If the refresh token is invalid, invalidate access token as well
			$account->oauth2_account->expires = 0;
			$account->oauth2_account->token = null;
			$account->oauth2_account->refreshToken = null;
			$account->save();
			go()->getDebugger()->error($e->getMessage());
		}
		catch(Exception $e) {
			go()->getDebugger()->error($e->getMessage());
		}
	}
}