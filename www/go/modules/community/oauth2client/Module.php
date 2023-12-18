<?php

namespace go\modules\community\oauth2client;

use go\core;
use go\core\exception\NotFound;
use go\core\http\Exception;
use go\core\model;
use go\core\orm\Property;
use go\core\webclient\CSP;
use GO\Email\Controller\AccountController;
use GO\Email\Controller\MessageController;
use GO\Email\Model\Account as ActiveRecordAccount;
use GO\Email\Model\Alias;
use go\modules\community\email\model\Account;
use go\modules\community\oauth2client\model\Oauth2Client;
use go\modules\community\oauth2client\model\Authenticator;
use go\modules\community\oauth2client\model\DefaultClient;
use go\modules\community\oauth2client\model\Oauth2Account;

/**
 * @copyright (c) 2021, Intermesh BV http://www.intermesh.nl
 * @author Joachim van de Haterd <jvdhaterd@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module
{
	public function getStatus() : string
	{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function getDependencies(): array
	{
		return ["legacy/email"];
	}

	protected function afterInstall(model\Module $model): bool
	{
		Authenticator::register();

		return parent::afterInstall($model);
	}

	public static function initListeners()
	{
		$c = new AccountController();
		$c->addListener('load', 'go\modules\community\oauth2client\Module', 'loadAccountSettings');
		$c->addListener('submit', 'go\modules\community\oauth2client\Module', 'saveAccountSettings');

		$m = new MessageController();
		$m->addListener('beforesend', 'go\modules\community\oauth2client\Module', 'beforeSend');
	}

	public static function onHead() {
		$clients = \go\modules\community\oauth2client\model\Oauth2Client::find()->where('openId', '=', true)->all();
		if(!count($clients)) {
			return;
		}
		echo '<script>Ext.override(go.login.LoginDialog, {

		initComponent: go.login.LoginDialog.prototype.initComponent.createSequence(function () {';

		foreach($clients as $client) {

			$def = DefaultClient::findById($client->defaultClientId);

			echo '
			this.addSignInButton({
				xtype: "button",
				width: "100%",
				cls: "oauth2client-signin-btn",
				iconCls: "oauth2client-login-'.$def->name.'",
				text: t("Sign in with {name}").replace("{name}", "'.addslashes($client->name).'"),
				handler: function() {
					document.location = BaseHref + \'go/modules/community/oauth2client/gauth.php/openid/'.$client->id.'\';
				}
			})
';

		}
		echo '})
	});</script>';

	}

	public function defineListeners()
	{
		Account::on(Property::EVENT_MAPPING, static::class, 'onMap');
		CSP::on(Csp::EVENT_CREATE, static::class, 'onCspCreate');
		core\App::on(core\App::EVENT_HEAD, static::class, 'onHead');
	}


	public static function onMap(core\orm\Mapping $mapping)
	{
		$mapping->addHasOne('oauth2_account', Oauth2Account::class, ['id' => 'accountId'], false);
	}


	public static function onCspCreate(CSP $csp)
	{
		$csp->add('default-src', trim('https://accounts.google.com', '/'))
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
	public static function loadAccountSettings($self, array &$response, ActiveRecordAccount &$model, array &$params)
	{
		$id = $model->id;
		$acct = Account::findById($id);
		if ($acct && $acct->oauth2_account) {
			$model->checkImapConnectionOnSave = false;
			$response['data']['oauth2_client_id'] = $acct->oauth2_account->oauth2ClientId;
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
		if (isset($params['oauth2_client_id']) && intval($params['oauth2_client_id']) > 0) {
			$acct = Account::findById($response['id']);
			$oauth2_account = $acct->oauth2_account;
			if(empty($oauth2_account)) {
				go()->getDbConnection()->insert('oauth2client_account', [
					'accountId' => $response['id'],
					'oauth2ClientId' => $params['oauth2_client_id']
				])->execute();
			} elseif($acct->oauth2_account->oauth2ClientId !== $params['oauth2_client_id']) {
				$acct->oauth2_account->oauth2ClientId = $params['oauth2_client_id'];
				$acct->save();
			}
		}
	}

	/**
	 * If this module is enabled AND the current account has an OAuth2 connection, check and renew the accesstoken as
	 * needed
	 *
	 * @param MessageController $self
	 * @param array $response
	 * @param \GO\Base\Mail\SmimeMessage $message
	 * @param \GO\Base\Mail\Mailer $mailer
	 * @param ActiveRecordAccount $activeRecordAccount
	 * @param Alias $alias
	 * @param array $params
	 * @return void
	 * @throws Exception
	 * @throws NotFound
	 */
	public static function beforeSend(MessageController $self, array $response, \GO\Base\Mail\SmimeMessage &$message,
	                                  \GO\Base\Mail\Mailer &$mailer , ActiveRecordAccount $activeRecordAccount, Alias $alias, array $params) {
		$account = Account::findById($activeRecordAccount->id); // Need the JMAP entity for this!
		if ($acctSettings = $account->oauth2_account) {
			$client = Oauth2Client::findById($acctSettings->oauth2ClientId);
			$tokenParams = [
				'access_token' => $acctSettings->token,
				'refresh_token' => $acctSettings->refreshToken,
				'expires_in' => $acctSettings->expires - time()
			];

			$client->maybeRefreshAccessToken($account, $tokenParams);
		}
	}
}
