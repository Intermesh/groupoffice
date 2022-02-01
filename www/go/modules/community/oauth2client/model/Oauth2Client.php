<?php

namespace go\modules\community\oauth2client\model;

use go\core\exception\NotFound;
use go\core\jmap\Entity;
use go\core\orm\Mapping;

final class Oauth2Client extends Entity
{

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var int
	 */
	public $defaultClientId;

	/**
	 * @var string
	 */
	public $clientSecret;

	/**
	 * @var string
	 */
	public $clientId;

	/**
	 * @var string
	 */
	public $projectId;


	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("oauth2client_oauth2client");
	}


	/**
	 * Mini factory for OAuth2 client providers
	 *
	 * @return mixed
	 * @throws NotFound
	 */
	public function getProvider()
	{
		$defaultClient = DefaultClient::findById($this->defaultClientId);
		$url = rtrim(go()->getSettings()->URL, '/');

		switch ($defaultClient->name) {
			case 'Google':
				$accessType = 'offline';
				$scopes = ['https://mail.google.com/'];
				$redirectUri = $url . '/go/modules/community/oauth2client/gauth.php/callback';
				break;
			default:
				throw new NotFound('Default client ' . $defaultClient->name . ' not supported');
				break;
		}
		$prvClsName = "League\\OAuth2\\Client\\Provider\\" . ucfirst($defaultClient->name);

		return new $prvClsName([
			'clientId' => $this->clientId,
			'clientSecret' => $this->clientSecret,
			'redirectUri' => $redirectUri,
			'accessType' => $accessType,
			'scopes' => $scopes
		]);

	}
}