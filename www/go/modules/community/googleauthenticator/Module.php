<?php
namespace go\modules\community\googleauthenticator;

use go\core;
use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\Settings;
use go\modules\community\googleauthenticator\model;
use go\core\model\Group;
use go\core\model\Module as ModuleModel;
use go\core\model\User;

class Module extends core\Module {

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

	public function autoInstall(): bool
	{
		return false;
	}

	public function getStatus(): string
	{
		return self::STATUS_DEPRECATED;
	}
}
