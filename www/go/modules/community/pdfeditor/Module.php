<?php

namespace go\modules\community\pdfeditor;

use go\core;

class Module extends core\Module {

	function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public static function getCategory(): string
	{
		return go()->t("Files", static::getPackage(), static::getName());
	}

	public function getDependencies(): array
	{
		return ['legacy/files'];
	}

	public function autoInstall(): bool
	{
		return true;
	}

}
