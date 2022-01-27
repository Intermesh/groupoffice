<?php

namespace go\modules\community\oauth2client\model;

use go\core\jmap\Entity;

final class Oauth2Client extends Entity
{
	/**
	 * @var int
	 */
	public $accountId;

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

	/**
	 * @var string
	 */
//	public $token;

	/**
	 * @var string
	 */
//	public $refreshToken;

	/**
	 * @var int Unix timestamp
	 */
//	public $expires;

	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("oauth2client_oauth2client");
	}
}