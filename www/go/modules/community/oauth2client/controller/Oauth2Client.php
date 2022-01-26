<?php

namespace go\modules\community\oauth2client\controller;


use go\core\Controller;
use go\core\Environment;
use go\core\http\Exception;
use go\modules\community\email\model\Account;
use go\modules\community\oauth2client\model;
use go\core\http\Response;
use League\OAuth2\Client\Provider\Google;
use go\core\webclient\Extjs3;

final class Oauth2Client extends Controller
{

	public function entityClass()
	{
		return model\Oauth2Client::class;
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
				$acct->oauth2Client->token = $token->getToken();
				$acct->oauth2Client->expires = $token->getExpires();

				if($refreshToken = $token->getRefreshToken()) {
					$acct->oauth2Client->refreshToken = $refreshToken;
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

			$str = '<div class="card"><h3>' . go()->t('Hello').'&nbsp;'.$ownerDetails->getFirstName().'</h3>' .
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
		\GO::session()->closeWriting();
		$r = \go\core\http\Response::get();
		$r->setHeader('Location', $authUrl);
		$r->sendHeaders();
		exit(0);
	}

	public function test(int $accountId)
	{
		$acct = Account::findById($accountId);
		echo $acct->username;
		exit(0);
	}

	/**
	 * Prepare ourselves a Google Provider
	 *
	 * @todo: make generic for multiple oauth2 default clients
	 * @todo: move to separate class
	 * @param int $accountId
	 * @return Google
	 * @throws \Exception
	 */
	private function getProvider(int $accountId): Google
	{
		$acct = Account::findById($accountId);
		$acctSettings = $acct->oauth2Client;
		$url = rtrim(go()->getSettings()->URL, '/');
		return new Google([
			'clientId' => $acctSettings->clientId,
			'clientSecret' => $acctSettings->clientSecret,
			'redirectUri' => $url . '/go/modules/community/oauth2client/gauth.php/callback',
			'accessType'   => 'offline',
			'scopes' => ['https://mail.google.com/']
		]);
	}
}
