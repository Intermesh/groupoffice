<?php
namespace go\modules\community\oidc\model;

use Exception;
use go\core\jmap\Entity;
use go\core\orm\Mapping;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

class Client extends Entity {
	public ?string $id;
	public string $name;
	protected string $clientSecret;
	public string $clientId;
	public string $url;
	private ?GenericProvider $oidc;
	private array $wellKnown;

	public function getClientSecret(): null|string
	{
		return null;
	}

	public function setClientSecret(string $secret): void
	{
		$this->clientSecret = $secret;
	}

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("oidc_client");
	}

	public static function getClientName(): string
	{
		return "OIDConnectClient";
	}


	/**
	 * Autodiscovers openid configuration
	 *
	 * @return array|mixed
	 * @throws Exception
	 */
	private function discover() {
		if(!isset($this->wellKnown)) {
			if(!isset($_SESSION['oidc_wellknown_' . $this->id])) {
					$url = rtrim($this->url, '/') . '/.well-known/openid-configuration';

				$httpClient = new \go\core\http\Client();
				$response = $httpClient->get($url);

				if ($response['status'] != 200) {
					throw new Exception("Could not discover OIDC provider");
				}
				$this->wellKnown = json_decode($response['body'], true);

				$_SESSION['oidc_wellknown_' . $this->id] = $this->wellKnown;
			} else {
				$this->wellKnown = $_SESSION['oidc_wellknown_' . $this->id];
			}

		}

		return $this->wellKnown;
	}

	private function getProvider(): GenericProvider
	{
		if(!isset($this->oidc)) {
			$wellKnown = $this->discover();

			$this->oidc = new GenericProvider([
				'clientId'          => $this->clientId,
				'clientSecret'      => $this->clientSecret,
				'redirectUri'       => go()->getAuthState()->getPageUrl() . '/community/oidc/auth',
				'urlAuthorize'      => $wellKnown['authorization_endpoint'],
				'urlAccessToken'    => $wellKnown['token_endpoint'],
				'urlResourceOwnerDetails' => $wellKnown['userinfo_endpoint'],
				'scopes'            => ['openid', 'profile', 'email'],
				'scopeSeparator' => ' ',
				// Proof Key for Code Exchange, when the provider supports it
				'pkceMethod' => in_array(GenericProvider::PKCE_METHOD_S256, $wellKnown['code_challenge_methods_supported'] ?? []) ? GenericProvider::PKCE_METHOD_S256 : null
			]);
		}
		return $this->oidc;
	}

	private ?AccessToken $accessToken = null;

	/**
	 * Authenticate the user with the OIDC provider
	 *
	 * @return bool
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
	 */
	public function authenticate() :bool {

		// Do a preemptive check to see if the provider has thrown an error from a previous redirect
		if (isset($_REQUEST['error'])) {
			$desc = isset($_REQUEST['error_description']) ? ' Description: ' . $_REQUEST['error_description'] : '';
			throw new Exception('Error: ' . $_REQUEST['error'] .$desc);
		}

		// If we have an authorization code then proceed to request a token
		if (isset($_REQUEST['code'])) {

			// The state and PKCE verifier can only be used once
			$state = $this->getOIDCState();
			$pkceCode = $_SESSION['oidc_pkce'] ?? null;
			unset($_SESSION['oidc_state'], $_SESSION['oidc_pkce']);

			if ($state === "" || !isset($_REQUEST['state']) || !is_string($_REQUEST['state']) || !hash_equals($state, $_REQUEST['state'])) {
				throw new Exception('Unable to determine state');
			}

			$provider = $this->getProvider();
			if (!empty($pkceCode)) {
				$provider->setPkceCode($pkceCode);
			}
			$this->accessToken = $provider->getAccessToken('authorization_code', [
				'code' => $_REQUEST['code']
			]);

			return true;
		}

		// redirect to authentication page of ID provider
		$provider = $this->getProvider();
		$authorizationUrl = $provider->getAuthorizationUrl([
			'scope' => ['openid', 'profile', 'email']
		]);

		$this->setOIDCState($provider->getState());
		$_SESSION['oidc_pkce'] = $provider->getPkceCode();

		header('Location: ' . $authorizationUrl);
		exit();
	}

	public function requestUserInfo() : array {

		if(!isset($this->accessToken)) {
			throw new Exception("No access token");
		}
		$provider = $this->getProvider();
		$userinfoEndPoint = $provider->getResourceOwnerDetailsUrl($this->accessToken);

		$request  = $provider->getAuthenticatedRequest("GET", $userinfoEndPoint, $this->accessToken);
		return $provider->getParsedResponse($request);
	}

	private function getOIDCState() : string {
		$state = $_SESSION['oidc_state'] ?? "";
		return is_string($state) ? $state : "";
	}

	private function setOIDCState(string $state) {
		$_SESSION['oidc_state'] = $state;
	}

}