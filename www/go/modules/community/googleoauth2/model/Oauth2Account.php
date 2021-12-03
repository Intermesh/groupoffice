<?php

namespace go\modules\community\googleoauth2\model;

use go\core\data\Model;
use go\core\http\Client;
use go\core\orm\Property;

class Oauth2Account extends Property
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


	protected static function defineMapping()
	{
		return parent::defineMapping()
			->addTable("oauth2_accounts");
	}
}