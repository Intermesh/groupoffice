<?php

namespace go\modules\community\pdfeditor;

use go\core;

class Module extends core\Module {

	function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function getDependencies(): array
	{
		return ['legacy/files'];
	}

}
