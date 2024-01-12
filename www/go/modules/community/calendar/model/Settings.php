<?php

namespace go\modules\community\calendar\model;

use go\core;

class Settings extends core\Settings
{
	public string $videoUri = 'https://meet.jit.si/';

	public bool $videoJwtEnabled = false;

	public string $videoJwtSecret = ''; //TODO: jitsiJwtSecret is leaked on the client side! I dont know how to prevent that...

	public string $videoJwtAppId = '';

}