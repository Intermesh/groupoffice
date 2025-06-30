<?php

namespace go\modules\community\oauth2client\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

final class Oauth2Account extends Property
{
	public ?string $accountId;


	public ?string $oauth2ClientId;

	/**
	 * @var string
	 */
	public $token;

	/**
	 * @var string
	 */
	public $refreshToken;

	/**
	 * @var int Unix timestamp
	 */
	public $expires;

	protected static function defineMapping() :Mapping
	{
		return parent::defineMapping()
			->addTable("oauth2client_account");
	}

}