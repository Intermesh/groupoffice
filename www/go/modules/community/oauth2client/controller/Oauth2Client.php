<?php

namespace go\modules\community\oauth2client\controller;


use go\core\auth\Authenticate;
use go\core\http\Exception;
use go\core\jmap\EntityController;
use go\core\model\User;
use go\core\webclient\Extjs3;
use go\modules\community\email\model\Account;
use go\modules\community\oauth2client\model;
use go\modules\community\oauth2client\provider\Azure;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;

use TheNetworg\OAuth2\Client\Token\AccessToken;

final class Oauth2Client extends EntityController
{

	protected function authenticate()
	{
		return true;
	}

	public function entityClass(): string
	{
		return model\Oauth2Client::class;
	}

	public function query(array $params)
	{
		return $this->defaultQuery($params);
	}

	public function get(array $params)
	{
		return $this->defaultGet($params);
	}

	public function set(array $params)
	{
		return $this->defaultSet($params);
	}

	public function changes(array $params)
	{
		return $this->defaultChanges($params);
	}

	/**
	 * Callback function for use by Google's OAuth2 server
	 *
	 * Recieve token, add refreshtoken and expiry date to current OAuth2 account record
	 *
	 * @throws Exception
	 * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
	 */
	public function callback()
	{
		if (!empty($_GET['error'])) {
			throw new Exception(500, 'Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES));
		}
//		if (!isset($_SESSION['accountId'])) {
//			throw new Exception(500, 'Invalid parameter');
//		}

		if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

			// State is invalid, possible CSRF attack in progress
			unset($_SESSION['oauth2state']);
			unset($_SESSION['accountId']);
			\GO::session()->closeWriting();
			throw new Exception(500, 'Invalid state');
		} else if(!empty($_SESSION['accountId'])) {
			$this->callbackEmailAccount();
		} else {
			$this->callbackOpenId();
		}
	}


	private function callbackOpenId() {
		$client = model\Oauth2Client::findById($_SESSION['oauth2clientId']);

		/**
		 * @var Azure $provider;
		 */
		$provider = $client->getProvider(['openid', 'profile', 'email']);

		$token = $provider->getAccessToken('authorization_code', [
			'code' => $_GET['code']
		]);

		$userinfoEndPoint = $provider->getResourceOwnerDetailsUrl($token);
		/*
		 * eg.
 * {
"sub": "OLu859SGc2Sr9ZsqbkG-QbeLgJlb41KcdiPoLYNpSFA",
"name": "Mikah Ollenburg", // all names require the “profile” scope.
"family_name": " Ollenburg",
"given_name": "Mikah",
"picture": "https://graph.microsoft.com/v1.0/me/photo/$value",
"email": "mikoll@contoso.com" // requires the “email” scope.
}
 */
		$request  = $provider->getAuthenticatedRequest("GET", $userinfoEndPoint, $token);
		$response = $provider->getParsedResponse($request);

		$this->createUser($response, $token, $client);
	}

	private function createUser(array $response, AccessTokenInterface $token, model\Oauth2Client $client) {
		$user = User::findOrCreateByUsername($response['email'], $response['email'], $response['name']);


		$auth = new Authenticate();
		$auth->setAuthenticated($user);


		$default = model\DefaultClient::findById($client->defaultClientId);

		//old framework code here
		$account = \GO\Email\Model\Account::model()->findSingleByAttributes(array(
			'host' => 'outlook.office365.com',
			'username' => $response['email'],
			'user_id' => $user->id
		));

		if(!$account) {
			/** @noinspection DuplicatedCode */
			$account = new \GO\Email\Model\Account();
			$account->user_id = $user->id;
			$account->host = $default->imapHost;
			$account->port = $default->imapPort;
			$account->username = $response['email'];
			$account->password = "***";
			$account->imap_encryption = $default->imapEncryption;

			$account->smtp_username = $response['email'];
			$account->smtp_password = "";
			$account->smtp_host = $default->smtpHost;
			$account->smtp_port = $default->smtpPort;
			$account->smtp_encryption = $default->smtpEncryption;

			//$account->mbroot = ??

			$wasNew = $account->getIsNew();
			$account->checkImapConnectionOnSave = false;

			if(!$account->save(true)){
				throw new Exception("Could not save e-mail account: ".implode("\n", $account->getValidationErrors()));
			}

			if($wasNew) {
				$account->addAlias($user->email, $user->displayName);
			}

			go()->getDbConnection()->replace('oauth2client_account', [
				'accountId' => $account->id,
				'oauth2ClientId' => $client->id,
				'token' => $token->getToken(),
				'refreshToken' => $token->getRefreshToken(),
				'expires' => $token->getExpires()
			])->execute();

			$this->auth($account->id);
		} else {
			unset($_SESSION['oauth2clientId']);
			header("Location: " . go()->getSettings()->URL);
		}


	}


	private function callbackEmailAccount() {
		$accountId = $_SESSION['accountId'];
		$provider = $this->getProvider($accountId);

		// Try to get an access token (using the authorization code grant)
		$token = $provider->getAccessToken('authorization_code', [
			'code' => $_GET['code']
		]);

		try {
			$acct = Account::findById($accountId);
			$acct->oauth2_account->token = $token->getToken();
			$acct->oauth2_account->expires = $token->getExpires();

			if ($refreshToken = $token->getRefreshToken()) {
				$acct->oauth2_account->refreshToken = $refreshToken;
			}

			if(!$acct->save()) {
				throw new Exception(500, "Unable to save token");
			}

			$account = \GO\Email\Model\Account::model()->findByPk($accountId);
			$account->createDefaultFolders();
			//$ownerDetails = $provider->getResourceOwner($token);
		} catch (\Exception $e) {
			// Failed to get user details
			exit('Something went wrong: ' . $e->getMessage());
		}
		unset($_SESSION['oauth2state']);
		unset($_SESSION['accountId']);

		if(isset($_SESSION['oauth2clientId'])) {
			//we did openid and email auth afterward
			unset($_SESSION['oauth2clientId']);
			header("Location: " . go()->getSettings()->URL);
		}else {

			$str = '<div class="card"><h3>' . go()->t('Hello', 'community', 'oauth2client') . '</h3>' .
				'<p>' . go()->t('OAuth2 authentication was successful.', 'community', 'oauth2client') . '</p>' .
				'<p><a href="javascript:window.close()">' . go()->t("Click here", 'community', 'oauth2client') . '</a>&nbsp;' .
				go()->t("to close this window.", 'community', 'oauth2client') . '</p></div>';

			$webClient = Extjs3::get();
			$webClient->renderPage($str, go()->t('Success'));
		}
	}


	/**
	 * Authenticate using google Oauth settings for current account ID
	 *
	 * @param int $accountId
	 * @throws Exception
	 */
	public function auth(int $accountId)
	{
		$_SESSION['accountId'] = $accountId;
		if (!$provider = $this->getProvider($accountId)) {
			throw new Exception(412, 'No OAuth2 client settings found for current email account.');
		}

		if (!empty($_GET['error'])) {
			throw new Exception(500, 'Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES));
		}

		// If we don't have an authorization code then get one
		$authUrl = $provider->getAuthorizationUrl();

		$_SESSION['oauth2state'] = $provider->getState();
		\GO::session()->closeWriting();
		$r = \go\core\http\Response::get();
		$r->setHeader('Location', $authUrl);
		$r->sendHeaders();
		exit(0);
	}


	/**
	 * Find provider for current account ID.
	 *
	 * @param int $accountId
	 * @return ?AbstractProvider
	 * @throws \go\core\exception\NotFound
	 */
	private function getProvider(int $accountId): ?AbstractProvider
	{
		$acct = Account::findById($accountId);
		if ($acctSettings = $acct->oauth2_account) {
			$client = model\Oauth2Client::findById($acctSettings->oauth2ClientId);

			return $client->getProvider();
		}
		return null;
	}

	/**
	 * http://localhost/go/modules/commmunity/oauth2client/gauth.php/openid
	 *
	 *
	 * sadshs-Ssd3-sd&
	 *
	 * @return void
	 * @throws \go\core\exception\NotFound
	 */
	public function openId($clientId) {

		$_SESSION['oauth2clientId'] = $clientId;

		$client = model\Oauth2Client::findById($_SESSION['oauth2clientId']);

		$provider = $client->getProvider(['openid', 'profile', 'email']);
		$url = $provider->getAuthorizationUrl();

		//$url .= '&login_hint=MSchering@txg8h.onmicrosoft.com';

		$_SESSION['oauth2state'] = $provider->getState();

		$r = \go\core\http\Response::get();
		$r->setHeader('Location', $url);
		$r->sendHeaders();
		exit(0);
	}

}
