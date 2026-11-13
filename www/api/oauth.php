<?php
require('../vendor/autoload.php');

use go\core\App;
use go\core\ErrorHandler;
use go\core\http\Router;
use go\core\jmap\State;
use go\core\model\OauthUser;
use go\core\oauth\server\repositories;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
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
	public function userinfo(): ResponseInterface
	{
		$request = ServerRequest::fromGlobals();
		$response = new Response();

		$server = OAuthServer::resourceServer();

		try {
			$request = $server->validateAuthenticatedRequest($request);
		} catch (OAuthServerException $exception) {
			ErrorHandler::logException($exception);
			return $exception->generateHttpResponse($response);
		} catch (Exception $exception) {
			ErrorHandler::logException($exception);
			return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
				->generateHttpResponse($response);
		}

		$userId = $request->getAttribute("oauth_user_id");

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
	 * @return ResponseInterface
	 * @throws Exception
	 */
	public function token(): ResponseInterface
	{
		$server = $this->getServer();

		$request = ServerRequest::fromGlobals();
		$response = new Response();

		try {
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
	->addRoute('/userinfo/', 'GET', OAuthController::class, 'userinfo')
	->addRoute('/token/', 'POST', OAuthController::class, 'token')
	->addRoute('/\.well-known\/openid-configuration/', 'GET', OAuthController::class, 'openIdConfiguration')
	->addRoute('/certs/', 'GET', OAuthController::class, 'certs')
	->run();