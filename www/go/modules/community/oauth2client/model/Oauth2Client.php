<?php

namespace go\modules\community\oauth2client\model;

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


	protected static function defineMapping() :Mapping
	{
		return parent::defineMapping()
			->addTable("oauth2client_oauth2client");
//			->addScalar('defaultClient', 'oauth2client_default_client', ['id' => 'defaultClientId']);
	}
}