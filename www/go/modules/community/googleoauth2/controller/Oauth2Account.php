<?php

namespace go\modules\community\googleoauth2\controller;


use go\core\Controller;
use go\core\http\Exception;
use go\modules\community\email\model\Account;
use go\modules\community\googleoauth2\model;
use League\OAuth2\Client\Provider\Google;

final class Oauth2Account extends Controller
{

	public function entityClass()
	{
		return model\Oauth2Account::class;
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
			unset($_SESSION['oauth2state']);
			throw new Exception('Invalid state');
		} else {
			// Try to get an access token (using the authorization code grant)
			$token = $provider->getAccessToken('authorization_code', [
				'code' => $_GET['code']
			]);

			try {
				$acct = Account::findById($accountId);
				$acct->googleOauth2->token = $token->getToken();
				$acct->googleOauth2->refreshToken = $token->getRefreshToken();
				$acct->googleOauth2->expires = $token->getExpires();
				$acct->save();
				// We got an access token, let's now get the owner details
				$ownerDetails = $provider->getResourceOwner($token);

				// Use these details to create a new profile
				printf(go()->t('Hello') . '&nbsp;%s!&nbsp;' . go()->t('OAuth2 authentication was successful.') . '&nbsp;', $ownerDetails->getFirstName());

			} catch (\Exception $e) {
				// Failed to get user details
				exit('Something went wrong: ' . $e->getMessage());
			}
			echo '<a href="javascript:window.close()">' . go()->t("Click here") . '</a> ' . go()->t("to close this window.");
			exit(0);
		}
	}


	/**
	 * Authenticate using google Oauth settings for current account ID
	 *
	 * @param int $accountId
	 * @throws \GO\Base\Exception\MissingParameter
	 * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
	 */
	public function auth(int $accountId)
	{
		\GO::session()->values['accountId'] = $accountId;
		$provider = $this->getProvider($accountId);

		if (!empty($_GET['error'])) {
			throw new Exception('Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES));
		}

		// If we don't have an authorization code then get one
		$authUrl = $provider->getAuthorizationUrl();

		\GO::session()->values['oauth2state'] = $provider->getState();
		$r = \go\core\http\Response::get();
		$r->setHeader('Location', $authUrl);
		$r->sendHeaders();
		exit;
	}


	/**
	 * Prepare ourselves a Google Provider
	 *
	 * @param int $accountId
	 * @return Google
	 * @throws \Exception
	 */
	private function getProvider(int $accountId): Google
	{
		$acct = Account::findById($accountId);
		$acctSettings = $acct->googleOauth2;
		$url = rtrim(go()->getSettings()->URL, '/');
		return new Google([
			'clientId' => $acctSettings->clientId,
			'clientSecret' => $acctSettings->clientSecret,
			'redirectUri' => $url . '/gauth/callback',
			'scopes' => ['https://mail.google.com/']
		]);
	}
}
