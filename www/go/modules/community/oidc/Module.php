<?php
namespace go\modules\community\oidc;

use BadRequestException;
use go\core\auth\Authenticate;
use go\core\auth\ExternalUserLink;
use go\core\http\Response;
use go\core\jmap\State;
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

	public function pageAuth(int|null $clientId = null)
	{
		//disable browser caching
		Response::get()->noCache();

		go()->setAuthState(new State());
		//go()->getAuthState()->getPageUrl();

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

		go()->getSettings()->allowRegistration = true;

		// Found by the subject at the provider. The e-mail address is only used to link a user the first time.
		$user = (new ExternalUserLink('oidc_user', $client->id))->findOrCreateUser($info);

		$auth = new Authenticate();
		$auth->setAuthenticated($user);

		header("Location: " . go()->getSettings()->URL);
		exit();
	}




	/**
	 * The clients for the sign-in buttons of the login screen
	 */
	public function getLoginData(): ?array
	{
		$clients = Client::find(['id', 'name'])->orderBy(['name' => 'ASC'])->all();
		if (!count($clients)) {
			return null;
		}

		return ['clients' => array_map(fn(Client $c) => ['id' => $c->id, 'name' => $c->name], $clients)];
	}

}
