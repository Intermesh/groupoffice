<?php
namespace go\modules\community\pwned\model;
use go\core;


class Settings extends core\Settings {
		
	/**
	 * Enable for user group
	 */
	public int $enableForGroupId = core\model\Group::ID_EVERYONE;

}
