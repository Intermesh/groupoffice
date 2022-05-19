<?php

namespace go\modules\community\oauth2client\controller;


use go\core\http\Exception;
use go\core\jmap\EntityController;
use go\core\webclient\Extjs3;
use go\modules\community\email\model\Account;
use go\modules\community\oauth2client\model;

final class Oauth2Client extends EntityController
{

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
			throw new Exception('Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES));
		}
		if (!isset(\GO::session()->values['accountId'])) {
			throw new Exception('Invalid parameter');
		}
		$accountId = \GO::session()->values['accountId'];
		$provider = $this->getProvider($accountId);

		if (empty($_GET['state']) || ($_GET['state'] !== \GO::session()->values['oauth2state'])) {

			// State is invalid, possible CSRF attack in progress
			unset(\GO::session()->values['oauth2state']);
			unset(\GO::session()->values['accountId']);
			\GO::session()->closeWriting();
			throw new Exception('Invalid state');
		} else {
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
				$acct->save();
				$ownerDetails = $provider->getResourceOwner($token);
			} catch (\Exception $e) {
				// Failed to get user details
				exit('Something went wrong: ' . $e->getMessage());
			}
			unset(\GO::session()->values['oauth2state']);
			unset(\GO::session()->values['accountId']);
			\GO::session()->closeWriting();

			$str = '<div class="card"><h3>' . go()->t('Hello') . '&nbsp;' . $ownerDetails->getFirstName() . '</h3>' .
				'<p>' . go()->t('OAuth2 authentication was successful.') . '</p>' .
				'<p><a href="javascript:window.close()">' . go()->t("Click here") . '</a>&nbsp;' .
				go()->t("to close this window.") . '</p></div>';

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
		\GO::session()->values['accountId'] = $accountId;
		if (!$provider = $this->getProvider($accountId)) {
			throw new Exception('No OAuth2 client settings found for current email account.');
		}

		if (!empty($_GET['error'])) {
			throw new Exception('Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES));
		}

		// If we don't have an authorization code then get one
		$authUrl = $provider->getAuthorizationUrl();

		\GO::session()->values['oauth2state'] = $provider->getState();
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
	 * @return mixed|null
	 * @throws \go\core\exception\NotFound
	 */
	private function getProvider(int $accountId)
	{
		$acct = Account::findById($accountId);
		if ($acctSettings = $acct->oauth2_account) {
			$client = model\Oauth2Client::findById($acctSettings->oauth2ClientId);

			return $client->getProvider();
		}
		return null;
	}
}
