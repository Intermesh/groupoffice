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
}