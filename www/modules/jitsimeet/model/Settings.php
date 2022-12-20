<?php

namespace GO\Jitsimeet\model;

use go\core;
use go\core\db\Query;

class Settings extends core\Settings
{
	/**
	 * @var string
	 */
	public $jitsiUri = 'https://meet.jit.si/';

	/**
	 * @var boolean
	 */
	public $jitsiJwtEnabled = false;

	/**
	 * @var string
	 */
	public $jitsiJwtSecret = ''; //TODO: jitsiJwtSecret is leaked on the client side! I dont know how to prevent that...

	/**
	 * @var string
	 */
	public $jitsiJwtAppId = '';

	public static function getModulePackageName(): ?string
	{
		return null; // backwards compat
	}

	public static function getModuleName(): string
	{
		return 'jitsimeet'; // backwards compat
	}
}