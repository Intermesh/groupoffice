<?php
require('../vendor/autoload.php');

use go\core\App;
use go\core\ErrorHandler;
use go\core\http\Router;
use go\core\jmap\State;
use go\core\model\OauthUser;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use go\core\oauth\server\ClientRegistration;
use go\core\oauth\server\OAuthServer;
use go\core\oauth\server\PendingAuthorization;
use Psr\Http\Message\ResponseInterface;

App::get();
go()->setAuthState(new State());

//for serializing authRequest
go()->sessionStart();

class OAuthController {

	private ?AuthorizationServer $server;

	/**
	 * @return AuthorizationServer
	 * @throws Exception
	 */
	private function getServer(): AuthorizationServer
	{
		if(!isset($this->server)) {
			$this->server = OAuthServer::authorizationServer();
		}

		return $this->server;
	}

	public function authorize() : ResponseInterface {
		$server = $this->getServer();

		$request = ServerRequest::fromGlobals();
		$response = new Response();
		
		go()->debug($request->getQueryParams());

		try {
			// Validate the HTTP request and return an AuthorizationRequest object.
			// The auth request object can be serialized into a user's session
			// Only resumed when the user returns from the login page
			$authRequest = PendingAuthorization::take($request->getQueryParams()) ?? OAuthServer::validateAuthorizationRequest($server, $request);

			if(!go()->getAuthState()->isAuthenticated()) {
				PendingAuthorization::save($authRequest);

				$loginUrl = dirname($_SERVER['PHP_SELF'], 3) . '?oauthAuthorize=1';
				return $response->withStatus(302)->withHeader('Location', $loginUrl);
			}

			$user = go()->getAuthState()->getUser(['username', 'displayName', 'id', 'email', 'modifiedAt']);

			if(OAuthServer::needsConsent($authRequest->getClient())) {
				$body = $request->getParsedBody();
				if($request->getMethod() === 'POST' && is_array($body) && isset($body['decision'])) {
					// The consent screen posts the decision with the CSRF token. Without it the user isn't authenticated
					// and is sent to the login page above.
					$data = json_decode((string) ($body['data'] ?? ''), true);
					return OAuthServer::completeConsent($server, $authRequest, new OauthUser($user), $body['decision'] === 'approve', is_array($data) ? $data : []);
				}

				// Ask the user in the web client
				PendingAuthorization::save($authRequest);
				$consentUrl = dirname($_SERVER['PHP_SELF'], 3) . '?oauthConsent=1';
				return $response->withStatus(302)->withHeader('Location', $consentUrl);
			}

			$authRequest->setUser(new OauthUser($user));

			// Once the user has approved or denied the client update the status
			// (true = approved, false = denied)
			$authRequest->setAuthorizationApproved(true);

			// Return the HTTP redirect response
			return $server->completeAuthorizationRequest($authRequest, $response);
		} catch (OAuthServerException $exception) {
			ErrorHandler::logException($exception);
			return $exception->generateHttpResponse($response);
		} catch (Exception $exception) {
			
			ErrorHandler::logException($exception);

			$body = new Stream(fopen('php://temp', 'rb+'));
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}

	/**
	 * @return ResponseInterface
	 * @throws Exception
	 */
	/**
	 * What the consent screen shows about the request that waits for the user's approval
	 */
	public function consent(): ResponseInterface
	{
		$response = new Response();
		$authRequest = PendingAuthorization::peek();
		if (!go()->getAuthState()->isAuthenticated() || !$authRequest) {
			return $response->withStatus(404);
		}

		$response->getBody()->write(json_encode(OAuthServer::consentDetails($authRequest)));
		return $response
			->withStatus(200)
			->withHeader('cache-control', 'no-store')
			->withHeader('content-type', 'application/json; charset=UTF-8');
	}

	/**
	 * Dynamic client registration (RFC 7591)
	 */
	public function register(): ResponseInterface
	{
		$response = new Response();
		if (!ClientRegistration::isAllowed()) {
			return $response->withStatus(404);
		}

		if (!ClientRegistration::mayRegister(\go\core\http\Request::get()->getRemoteIpAddress())) {
			return $this->json($response, 429, ['error' => 'too_many_requests']);
		}

		$metadata = json_decode((string) ServerRequest::fromGlobals()->getBody(), true);
		if (!is_array($metadata)) {
			return $this->json($response, 400, ['error' => 'invalid_client_metadata']);
		}

		try {
			return $this->json($response, 201, ClientRegistration::register($metadata));
		} catch (InvalidArgumentException $e) {
			return $this->json($response, 400, ['error' => $e->getMessage()]);
		}
	}

	private function json(Response $response, int $status, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES));
		return $response->withStatus($status)
			->withHeader('cache-control', 'no-store')
			->withHeader('content-type', 'application/json; charset=UTF-8');
	}

	public function userinfo(): ResponseInterface
	{
		return OAuthServer::userInfo(ServerRequest::fromGlobals(), OAuthServer::resourceServer());
	}

	/**
	 * @return ResponseInterface
	 * @throws Exception
	 */
	public function token(): ResponseInterface
	{
		$server = $this->getServer();

		$request = ServerRequest::fromGlobals();
		$response = new Response();

		try {
			OAuthServer::validateResource($request->getParsedBody()['resource'] ?? null);
			return $server->respondToAccessTokenRequest($request, $response);
		} catch (OAuthServerException $exception) {
			ErrorHandler::logException($exception);
			return $exception->generateHttpResponse($response);
		} catch (Exception $exception) {
			ErrorHandler::logException($exception);
			$body = new Stream(fopen('php://temp', 'rb+'));
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}

	/**
	 * @return Response
	 * @noinspection PhpUnused
	 */
	public function openIdConfiguration(): Response
	{
		$discovery = OAuthServer::metadata();

		$response = new Response();

		//write to body
		$response->getBody()->write(json_encode($discovery, JSON_PRETTY_PRINT));

		//return to output
		return $response
			->withStatus(200)
			->withHeader('content-type', 'application/json; charset=UTF-8');
	}

	/**
	 * @return ResponseInterface
	 */
	public function certs()
	{
		$response = new Response();

		try {
			$keys = [
				'keys' => [],
			];

			//create jwk from public key
			$pubKey = OAuthServer::publicKeyFile()->getContents();
			$keyInfo = openssl_pkey_get_details(openssl_pkey_get_public($pubKey));

			//get public key
			$keys['keys'][] =  [
				'n' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyInfo['rsa']['n'])), '='),
				'e' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyInfo['rsa']['e'])), '='),
				'use' => 'sig',
				'alg' => 'RS256',
				'kty' => 'RSA',
				'kid' => OAuthServer::kid(),
			];

			//write to body
			$response->getBody()->write(json_encode($keys, JSON_PRETTY_PRINT));

			//return to output
			return $response
				->withStatus(200)
				->withHeader('content-type', 'application/json; charset=UTF-8');
		} catch (Exception $exception) {

			ErrorHandler::logException($exception);
			$body = new Stream(fopen('php://temp', 'rb+'));
			$body->write($exception->getMessage());

			return $response->withStatus(500)->withBody($body);
		}
	}
}

/** @noinspection PhpUnhandledExceptionInspection */
(new Router())
	->addRoute('/authorize/', 'GET', OAuthController::class, 'authorize')
	->addRoute('/authorize/', 'POST', OAuthController::class, 'authorize')
	->addRoute('/consent/', 'GET', OAuthController::class, 'consent')
	->addRoute('/register/', 'POST', OAuthController::class, 'register')
	->addRoute('/userinfo/', 'GET', OAuthController::class, 'userinfo')
	->addRoute('/token/', 'POST', OAuthController::class, 'token')
	->addRoute('/\.well-known\/openid-configuration/', 'GET', OAuthController::class, 'openIdConfiguration')
	->addRoute('/certs/', 'GET', OAuthController::class, 'certs')
	->run();