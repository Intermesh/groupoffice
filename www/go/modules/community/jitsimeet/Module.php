<?php

namespace go\modules\community\jitsimeet;

use go\core;
use go\modules\community\jitsimeet\model\Settings;

/**
 * @copyright (c) 2025, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module
{
	public function getStatus() : string
	{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function getDependencies(): array
	{
		return ["community/calendar"];
	}

	public function getSettings()
	{
		return Settings::get();
	}
}
