<?php
require('../vendor/autoload.php');
use go\core\App;
use go\core\exception\ConfigurationException;
use go\core\fs\File as FileAlias;
use go\core\jmap\State;
use go\core\http\Router;
use go\core\model\OauthUser as UserAlias;
use go\core\oauth\server\repositories;
use GuzzleHttp\Psr7\MessageTrait as MessageTraitAlias;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream as StreamAlias;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token as TokenAlias;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use Psr\Http\Message\ResponseInterface as ResponseInterfaceAlias;

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

				$loginUrl = dirname(dirname(dirname($_SERVER['PHP_SELF']))) . '?authRedirectUrl=' . urlencode($authRedirectUrl);
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


			$body = new StreamAlias(fopen('php://temp', 'r+'));
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

	public function userinfo() {

		$token = $this->validateAccessToken($_REQUEST['access_token']);

		$userId = $token->getClaim('sub');

		$userRepository = new repositories\UserRepository();
		$userEntity = $userRepository->getUserEntityByIdentifier($userId);

		$data = $userEntity->getClaims();
		$data['sub'] = $userEntity->getIdentifier();

		return json_encode($data);
	}

	public function token() {
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
			$body = new StreamAlias(fopen('php://temp', 'r+'));
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}
}

(new Router())
	->addRoute('/authorize/', 'GET', OAuthController::class, 'authorize')
	->addRoute('/userinfo/', 'GET', OAuthController::class, 'userinfo')
	->addRoute('/token/', 'POST', OAuthController::class, 'token')
	->run();