<?php
namespace go\modules\community\oauth2client\provider;

class Azure extends \TheNetworg\OAuth2\Client\Provider\Azure {
	public function getResourceOwnerDetailsUrl(\League\OAuth2\Client\Token\AccessToken $token): string
	{
		$openIdConfiguration = $this->getOpenIdConfiguration($this->tenant, $this->defaultEndPointVersion);
		return $openIdConfiguration['userinfo_endpoint'];
	}

}