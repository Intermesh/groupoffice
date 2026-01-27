<?php
namespace go\modules\community\oidc\model;

use go\core\jmap\Entity;
use go\core\orm\Mapping;
use Jumbojett\OpenIDConnectClient;

class Client extends Entity {
	public ?string $id;
	public string $name;
	protected string $clientSecret;
	public string $clientId;
	public string $url;
	private ?OpenIDConnectClient $oidc;

	public function getClientSecret(): null|string
	{
		return null;
	}

	public function setClientSecret(string $secret): void
	{
		$this->clientSecret = $secret;
	}

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("oidc_client");
	}

	public static function getClientName(): string
	{
		return "OIDConnectClient";
	}

	private function getClient(): OpenIDConnectClient
	{
		if(!isset($this->oidc)) {
			$this->oidc = new OpenIDConnectClient(
				$this->url,
				$this->clientId,
				$this->clientSecret
			);
			$this->oidc->setRedirectURL(go()->getAuthState()->getPageUrl() . '/community/oidc/auth');
		}
		return $this->oidc;
	}

	public function authenticate() {

		$oidc = $this->getClient();
		$oidc->addScope(['profile','email']);

		$oidc->authenticate();
	}

	public function requestUserInfo() : \stdClass {
		return $this->getClient()->requestUserInfo();
	}
}