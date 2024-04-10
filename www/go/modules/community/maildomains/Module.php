<?php

namespace go\modules\community\maildomains;

use go\core;
use go\core\model\Module as ModuleModel;

final class Module extends core\Module
{
	public function getStatus() : string{
		return self::STATUS_BETA;
	}
	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

}
