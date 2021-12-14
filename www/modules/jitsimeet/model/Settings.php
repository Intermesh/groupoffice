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

	public static function getModulePackageName(): ?string
	{
		return null; // backwards compat
	}

	public static function getModuleName(): string
	{
		return 'jitsimeet'; // backwards compat
	}
}