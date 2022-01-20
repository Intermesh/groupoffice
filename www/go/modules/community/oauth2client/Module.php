<?php
namespace go\modules\community\oauth2client;
							
use go\core;
use go\core\orm\Property;
use go\core\webclient\CSP;
use go\modules\community\email\model\Account;
use GO\Email\Controller\AccountController;
use GO\Email\Model\Account as ActiveRecordAccount;
use go\modules\community\oauth2client\model\Oauth2Client;

/**						
 * @copyright (c) 2021, Intermesh BV http://www.intermesh.nl
 * @author Joachim van de Haterd <jvdhaterd@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module
{
							
	public function getAuthor() :string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function getDependencies() :array
	{
		return ["legacy/email"];
	}

	public static function initListeners()
	{
		$c = new AccountController();
		$c->addListener('load', 'go\modules\community\oauth2client\Module', 'loadAccountSettings');
		$c->addListener( 'submit', 'go\modules\community\oauth2client\Module', 'saveAccountSettings');
	}

	public function defineListeners()
	{
		Account::on(Property::EVENT_MAPPING, static::class, 'onMap');
		CSP::on(Csp::EVENT_CREATE,  static::class, 'onCspCreate');
	}


	public static function onMap(core\orm\Mapping $mapping)
	{
		$mapping->addHasOne('oauth2Client', Oauth2Client::class, ['id' => 'accountId'], false);
	}


	public static function onCspCreate(CSP $csp)
	{
		$csp->add('default-src',  trim('https://accounts.google.com', '/'))
			->add("connect-src", "'self'")
			->add("connect-src", trim('https://accounts.google.com', '/'));

	}

	/**
	 * Upon loading an account, try to load Oauth2 client settings as well
	 *
	 * @param $self
	 * @param array $response
	 * @param ActiveRecordAccount $model
	 * @param array $params
	 * @throws \Exception
	 */
	public static function loadAccountSettings($self, array &$response, ActiveRecordAccount &$model,array &$params)
	{
		$id = $model->id;
		$acct = Account::findById($id);
		if ($acct && $acct->oauth2Client) {
			$response['data']['clientSecret'] = $acct->oauth2Client->clientSecret;
			$response['data']['clientId'] = $acct->oauth2Client->clientId;
			$response['data']['projectId'] = $acct->oauth2Client->projectId;
			$response['data']['refreshToken'] = $acct->oauth2Client->refreshToken;
		}
	}

	/**
	 * After saving an account, save the oauth2 client settings as well
	 *
	 * @param AccountController $self
	 * @param array $response
	 * @param ActiveRecordAccount $model
	 * @param array $params
	 * @param array $modifiedAttributes
	 * @throws \Exception
	 */
	public static function saveAccountSettings(AccountController $self, array &$response, ActiveRecordAccount &$model, array $params, array $modifiedAttributes)
	{
		if(isset($params['clientId']) && intval($params['default_client_id']) > 0) {
			$acct = Account::findById($response['id']);
			$acct->oauth2Client->clientId = $params['clientId'];
			$acct->oauth2Client->clientSecret = $params['clientSecret'];
			$acct->oauth2Client->projectId = $params['projectId'];
			$acct->save();
		}
	}
}
