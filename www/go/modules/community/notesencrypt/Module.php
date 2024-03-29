<?php
namespace go\modules\community\notesencrypt;

use go\core;

class Module extends core\Module {

	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

}
