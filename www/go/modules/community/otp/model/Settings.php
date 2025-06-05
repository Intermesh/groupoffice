<?php
namespace go\modules\community\otp\model;
use go\core;


class Settings extends core\Settings {
		
	/**
	 * Enforce for user group
	 * 
	 * @var ?int
	 */
	public ?int $enforceForGroupId = null;

	/**
	 * Countdown before user can use Group-Office
	 *
	 * @var int seconds
	 */
	public int $countDown = 10;

	/**
	 * Block Group-Office usage until setup is complete
	 *
	 * @var bool
	 */
	public bool $block = false;
}
