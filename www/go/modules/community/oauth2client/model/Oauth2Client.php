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
		$prvVendorName = 'League';
		$params = [
			'clientId' => $this->clientId,
			'clientSecret' => $this->clientSecret,
			'redirectUri' => $url . '/go/modules/community/oauth2client/gauth.php/callback',
		];
		switch ($defaultClient->name) {
			case 'Google':
				$params['accessType'] = 'offline';
				$params['scopes'] = ['https://mail.google.com/'];
				break;
			case 'Azure':
				// https://docs.microsoft.com/en-us/exchange/client-developer/legacy-protocols/how-to-authenticate-an-imap-pop-smtp-application-by-using-oauth
				// https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-auth-code-flow
				$prvVendorName = 'TheNetworg';
				$params['tenant'] = $this->projectId;
				$params['scopes'] = [
					'openid',
					'profile',
					'offline_access',
					'email',
					'https://outlook.office.com/IMAP.AccessAsUser.All',
					'https://outlook.office.com/SMTP.Send'
				];
				$params['defaultEndPointVersion'] = '2.0';
				break;
			default:
				throw new NotFound('Default client ' . $defaultClient->name . ' not supported');
				break;
		}
		$prvClsName = $prvVendorName . "\\OAuth2\\Client\\Provider\\" . ucfirst($defaultClient->name);

		return new $prvClsName($params);

	}
}