<?php

namespace go\modules\community\tempsieve;

use go\core;
use go\core\model;

class Module extends core\Module
{


	/**
	 * @inheritDoc
	 */
	function getAuthor(): string
	{
		return 'Intermesh BV';
	}

	/**
	 * @return string[]
	 */
	public function getDependencies() : array
	{
		return ['legacy/email'];
	}

	public function getStatus(): string
	{
		return self::STATUS_BETA;
	}

	public function autoInstall(): bool
	{
		return false;
	}



}