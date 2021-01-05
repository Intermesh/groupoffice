<?php
require('../vendor/autoload.php');

use go\core\App;
use go\core\fs\File as FileAlias;
use go\core\jmap\State;
use go\core\http\Router;
use go\core\model\OauthUser as UserAlias;

use go\core\oauth\server\AuthorizationServer;
use go\core\oauth\server\grant\AuthCodeGrant;
use go\core\oauth\server\repositories;

use go\core\oauth\server\responsetypes\IdTokenResponse;
use GuzzleHttp\Psr7\MessageTrait as MessageTraitAlias;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream as StreamAlias;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token as TokenAlias;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\Exception\OAuthServerException;

use League\OAuth2\Server\Grant\RefreshTokenGrant;
use OpenIDConnectServer\ClaimExtractor;
use Psr\Http\Message\ResponseInterface as ResponseInterfaceAlias;
use Jose\Factory\JWKFactory;

App::get();
App::get()->setAuthState(new State());

//for serializing authRequest
session_name('groupoffice_oauth');
session_start();

class OAuthController {

	/**
	 * @var  AuthorizationServer
	 */
	private $server;

	/**
	 * @return AuthorizationServer
	 * @throws Exception
	 */
	private function getServer() {

		if(!isset($this->server)) {
			// Init our repositories
			$clientRepository = new repositories\ClientRepository();
			$scopeRepository = new repositories\ScopeRepository();
			$accessTokenRepository = new repositories\AccessTokenRepository();
			$authCodeRepository = new repositories\AuthCodeRepository();
			$refreshTokenRepository = new repositories\RefreshTokenRepository();

			$privateKeyPath = 'file://' . $this->getPrivateKeyFile()->getPath();

			// OpenID Connect Response Type
			$responseType = new IdTokenResponse(new repositories\UserRepository(), new ClaimExtractor());

			$this->server = new AuthorizationServer(
				$clientRepository,
				$accessTokenRepository,
				$scopeRepository,
				$privateKeyPath,
				'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen',
				$responseType
			);

			// Enable the authentication code grant on the server with a token TTL of 1 hour
			$this->server->enableGrantType(
				new AuthCodeGrant(
					$authCodeRepository,
					$refreshTokenRepository,
					new DateInterval('PT10M')
				),
				new DateInterval('PT1H')
			);

			// Enable the refresh token grant on the server
			$refreshGrant = new RefreshTokenGrant($refreshTokenRepository);
			$refreshGrant->setRefreshTokenTTL(new \DateInterval('P1M'));

			$this->server->enableGrantType(
				$refreshGrant,
				new DateInterval('PT1H')
			);
		}

		return $this->server;
	}

	/**
	 * @return MessageTraitAlias|Response|ResponseInterfaceAlias
	 * @throws Exception
	 */
	public function authorize() {
		$server = $this->getServer();

		$request = ServerRequest::fromGlobals();
		$response = new Response();

		try {
			// Validate the HTTP request and return an AuthorizationRequest object.
			// The auth request object can be serialized into a user's session
			$authRequest = $_SESSION['authRequest'] ?? $server->validateAuthorizationRequest($request);

			unset($_SESSION['authRequest']);

			if(!go()->getAuthState()->isAuthenticated()) {
				$_SESSION['authRequest'] = $authRequest;
				$authRedirectUrl = $_SERVER['PHP_SELF'] . '/authorize';

				$loginUrl = dirname($_SERVER['PHP_SELF'], 3) . '?authRedirectUrl=' . urlencode($authRedirectUrl);
				return $response->withStatus(302)->withHeader('Location', $loginUrl);
			}

			$user = go()->getAuthState()->getUser(['username', 'displayName', 'id', 'email', 'modifiedAt']);
			$authRequest->setUser(new UserAlias($user));


//			$userRepository = new repositories\UserRepository();
//			$userEntity = $userRepository->getUserEntityByIdentifier(2);
//			$authRequest->setUser($userEntity);

			// Once the user has approved or denied the client update the status
			// (true = approved, false = denied)
			$authRequest->setAuthorizationApproved(true);

			// Return the HTTP redirect response
			return $server->completeAuthorizationRequest($authRequest, $response);
		} catch (OAuthServerException $exception) {
			return $exception->generateHttpResponse($response);
		} catch (Exception $exception) {


			$body = new StreamAlias(fopen('php://temp', 'rb+'));
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}

//	private function validateResourceRequest(\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
//
//
//		// Init our repositories
//		$accessTokenRepository = new repositories\AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
//
//// Path to authorization server's public key
//		$publicKeyPath = 'file://' . go()->getEnvironment()->getInstallPath() . '/public.key';
//
//// Setup the authorization server
//		$server = new \League\OAuth2\Server\ResourceServer(
//			$accessTokenRepository,
//			$publicKeyPath
//		);
//
//		return $server->validateAuthenticatedRequest($request);
//
//	}

	/**
	 * @return FileAlias
	 * @throws Exception
	 */
	private function getPrivateKeyFile() {
		$file = go()->getDataFolder()->getFile('oauth2/private.key');
		if(!$file->exists()) {

			$file->getFolder()->create();

			$private = openssl_pkey_new();
			if(!openssl_pkey_export_to_file($private, $file->getPath())) {
				throw new Exception ("Could not create private key file");
			}
			$file->chmod(0600);

			$details = openssl_pkey_get_details($private);

			$pubkey = $details["key"];
			$pubKeyFile = go()->getDataFolder()->getFile('oauth2/public.key');
			if(!$pubKeyFile->putContents($pubkey)) {
				throw new Exception ("Could not create public key file");
			}
			$pubKeyFile->chmod(0600);
		}



		return $file;
	}

	/**
	 * @return FileAlias
	 * @throws Exception
	 */
	private function getPublicKeyFile() {
		$file = go()->getDataFolder()->getFile('oauth2/public.key');
		if(!$file->exists()) {
			$this->getPrivateKeyFile();
		}
		return $file;
	}

	/**
	 * @param $jwt
	 * @return TokenAlias
	 * @throws OAuthServerException
	 */
	private function validateAccessToken($jwt) {
		try {
			// Attempt to parse and validate the JWT
			$token = (new Parser())->parse($jwt);
			$publicKeyPath = 'file://' . $this->getPublicKeyFile()->getPath();
//			$publicKey = new CryptKey($publicKeyPath);
			try {
				if ($token->verify(new Sha256(), $publicKeyPath) === false) {
					throw OAuthServerException::accessDenied('Access token could not be verified');
				}
			} catch (BadMethodCallException $exception) {
				throw OAuthServerException::accessDenied('Access token is not signed', null, $exception);
			}

			// Ensure access token hasn't expired
			$data = new ValidationData();
			$data->setCurrentTime(time());

			if ($token->validate($data) === false) {
				throw OAuthServerException::accessDenied('Access token is invalid');
			}
		} catch (InvalidArgumentException $exception) {
			// JWT couldn't be parsed so return the request as is
			throw OAuthServerException::accessDenied($exception->getMessage(), null, $exception);
		} catch (RuntimeException $exception) {
			// JWT couldn't be parsed so return the request as is
			throw OAuthServerException::accessDenied('Error while decoding to JSON', null, $exception);
		}

		$accessTokenRepository = new repositories\AccessTokenRepository();
		// Check if token has been revoked
		if ($accessTokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
			throw OAuthServerException::accessDenied('Access token has been revoked');
		}

		return $token;
	}

	/**
	 * @return MessageTraitAlias
	 * @throws OAuthServerException
	 */
	public function userinfo()
	{
		$request = ServerRequest::fromGlobals();
		$response = new Response();

		$accessToken = null;
		$authorizationHeaders = $request->getHeader('Authorization');

		if (count($authorizationHeaders)) {
			$authorizationHeader = trim($authorizationHeaders[0]);

			if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
				$accessToken = $matches[1];
			}
		}

		if (null === $accessToken) {
			$accessToken = isset($_REQUEST['access_token']) ? $_REQUEST['access_token'] : null;
		}

		if (null === $accessToken) {
			throw OAuthServerException::accessDenied('Access token is invalid');
		}

		$token = $this->validateAccessToken($accessToken);

		$userId = $token->getClaim('sub');

		$userRepository = new repositories\UserRepository();
		$userEntity = $userRepository->getUserEntityByIdentifier($userId);

		$data = $userEntity->getClaims();
		$data['sub'] = $userEntity->getIdentifier();

		$response->getBody()->write(json_encode($data));
		return $response
			->withStatus(200)
			->withHeader('pragma', 'no-cache')
			->withHeader('cache-control', 'no-store')
			->withHeader('content-type', 'application/json; charset=UTF-8');
	}

	/**
	 * @return MessageTraitAlias|Response|ResponseInterfaceAlias
	 * @throws Exception
	 */
	public function token()
	{
		$server = $this->getServer();

		$request = ServerRequest::fromGlobals();
		$response = new Response();

		try {
			return $server->respondToAccessTokenRequest($request, $response);
		} catch (OAuthServerException $exception) {
			go()->debug($exception);
			return $exception->generateHttpResponse($response);
		} catch (Exception $exception) {
			go()->debug($exception);
			$body = new StreamAlias(fopen('php://temp', 'rb+'));
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}

	/**
	 * @return MessageTraitAlias
	 */
	public function openIdConfiguration()
	{
		$goUrl = rtrim(go()->getSettings()->URL, '/') . '/';
		$endpointBase = $goUrl . 'api/oauth.php';

		$signing_alg_values_supported = array('HS256', 'RS256');

		$discovery = array(
			'issuer' => $goUrl,
			'authorization_endpoint' => $endpointBase . '/authorize',
			'token_endpoint' => $endpointBase . '/token',
			'userinfo_endpoint' => $endpointBase . '/userinfo',
			'jwks_uri' => $endpointBase . '/certs',
			'scopes_supported' => array('openid'),
			'response_types_supported' => array('code', 'code token', 'code id_token', 'token', 'id_token token', 'code id_token token', 'id_token'),
			'grant_types_supported' => array('authorization_code', "refresh_token"), //TODO - get enabled grant types from server
			'acr_values_supported' => array(),
			'subject_types_supported' => array('pairwise'),

			'userinfo_signing_alg_values_supported' => $signing_alg_values_supported,

			'id_token_signing_alg_values_supported' => $signing_alg_values_supported,

			'token_endpoint_auth_methods_supported' => array('client_secret_post', 'client_secret_basic', 'client_secret_jwt', 'private_key_jwt'),
			'token_endpoint_auth_signing_alg_values_supported' => $signing_alg_values_supported,

			'display_values_supported' => array('page'),
			'claim_types_supported' => array('normal'),
			'claims_supported' => array(
				'aud',
				'iss',
				'iat',
				'exp',
				'nonce',
			),
		);

		$response = new Response();

		//write to body
		$response->getBody()->write(json_encode($discovery, JSON_PRETTY_PRINT));

		//return to output
		return $response
			->withStatus(200)
			->withHeader('content-type', 'application/json; charset=UTF-8');
	}

	/**
	 * @return MessageTraitAlias|Response
	 */
	public function certs()
	{
		$response = new Response();

		try {
			$keys = [
				'keys' => [],
			];

			$key = $this->getPrivateKeyFile()->getContents();

			//create jwk from private key
			$jwk = JWKFactory::createFromKey($key, null, [
				'use' => 'sig',
				'alg' => 'RS256',
				'kid' => md5($key),
			]);

			//get public key
			$keys['keys'][] = $jwk->toPublic();

			//write to body
			$response->getBody()->write(json_encode($keys, JSON_PRETTY_PRINT));

			//return to output
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json; charset=UTF-8');
		} catch (\Exception $exception) {

			go()->debug($exception);
			$body = new StreamAlias(fopen('php://temp', 'rb+'));
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}
}

(new Router())
	->addRoute('/authorize/', 'GET', OAuthController::class, 'authorize')
	->addRoute('/userinfo/', 'GET', OAuthController::class, 'userinfo')
	->addRoute('/token/', 'POST', OAuthController::class, 'token')
	->addRoute('/\.well-known\/openid-configuration/', 'GET', OAuthController::class, 'openIdConfiguration')
	->addRoute('/certs/', 'GET', OAuthController::class, 'certs')
	->run();