<?php

namespace go\modules\community\webpush\model;

use go\core;

class Settings extends core\Settings {
	public string $vapidPublicKey = '';
	public string $vapidPrivateKey = '';
	//public string $vapidSubject = '';

}
