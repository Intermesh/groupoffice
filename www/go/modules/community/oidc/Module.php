<?php
namespace go\modules\community\oidc;

require __DIR__ . '/vendor/autoload.php';

use BadRequestException;
use go\core\App;
use go\core\auth\Authenticate;
use go\core\http\Response;
use go\core\jmap\State;
use go\core\model\User;
use go\modules\community\oidc\model\Authenticator;
use go\modules\community\oidc\model\Client;
class Module extends \go\core\Module
{
	public function getStatus() : string
	{
		return self::STATUS_STABLE;
	}

	public static function getCategory(): string
	{
		return go()->t("Authentication", static::getPackage(), static::getName());
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	protected function afterInstall(\go\core\model\Module $model): bool
	{
		Authenticator::register();

		return parent::afterInstall($model);
	}

	public function defineListeners()
	{
		App::on(App::EVENT_HEAD, static::class, 'onHead');
	}
	public function pageAuth(int|null $clientId = null)
	{
		//disable browser caching
		Response::get()->noCache();

		go()->setAuthState(new State());
		go()->getAuthState()->getPageUrl();

		go()->sessionStart();

		if(isset($clientId)) {
			$_SESSION['oidc_client_id'] = $clientId;
		}

		if(empty($_SESSION['oidc_client_id'])) {
			throw new BadRequestException("No client ID given");
		}

		$client = Client::findById($_SESSION['oidc_client_id']);

		$client->authenticate();

		$info = $client->requestUserInfo();

		if(empty($info->email)) {
			var_dump($info);
			throw new \Exception("Could not get e-mail from openid provider");
		}

		go()->getSettings()->allowRegistration = true;

		$user = User::findOrCreateByUsername($info->email, $info->email, $info->name);

		//register as oauth user
		go()->getDbConnection()->insertIgnore("oidc_user", ['userId' => $user->id, 'clientId' => $client->id])->execute();

		$auth = new Authenticate();
		$auth->setAuthenticated($user);

		header("Location: " . go()->getSettings()->URL);
		exit();
	}




	public static function onHead() {
		$clients = Client::find()->orderBy(['name' => 'DESC'])->all();
		if(!count($clients)) {
			return;
		}
		echo '<script>Ext.override(go.login.LoginDialog, {

		initComponent: go.login.LoginDialog.prototype.initComponent.createSequence(function () {';

		foreach($clients as $client) {
			echo '
			this.addSignInButton({
				xtype: "button",
				width: "100%",
				cls: "oidc-signin-btn",
				text: t("Sign in with {name}").replace("{name}", "'.addslashes($client->name).'"),
				handler: function() {
					document.location = BaseHref + \'api/page.php/community/oidc/auth/'.$client->id.'\';
				}
			})
';

		}
		echo '})
	});</script>';

	}

}
