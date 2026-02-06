<?php

namespace go\modules\community\emailfavorites;

class Module extends \go\core\Module {
	function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function autoInstall(): bool
	{
		return false;
	}

	public static function getCategory(): string
	{
		return go()->t("E-mail", static::getPackage(), static::getName());
	}
}