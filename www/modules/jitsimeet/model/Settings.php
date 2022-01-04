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

	protected function getModulePackageName()
	{
		return null; // backwards compat
	}

	protected function getModuleName() {
		return 'jitsimeet'; // backwards compat
	}
}