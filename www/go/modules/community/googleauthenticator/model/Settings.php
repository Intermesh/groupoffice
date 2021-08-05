<?php
namespace go\modules\community\googleauthenticator\model;
use go\core;


class Settings extends core\Settings {
		
	/**
	 * Enforce for user group
	 * 
	 * @var bool
	 */
	public $enforceForGroupId = null;

	/**
	 * Countdown before user can use Group-Office
	 *
	 * @var int seconds
	 */
	public $countDown = 10;

	/**
	 * Block Group-Office usage until setup is complete
	 *
	 * @var bool
	 */
	public $block = false;
}
