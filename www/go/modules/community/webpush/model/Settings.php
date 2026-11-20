<?php

namespace go\modules\community\webpush\model;

use go\core;

class Settings extends core\Settings
{
	public string $vapidPublicKey = '';
	public string $vapidPrivateKey = '';
	//public string $vapidSubject = '';

	/**
	 * The private VAPID key signs the push messages, so it's never sent to the client
	 */
	protected static function writeOnlyProperties(): array
	{
		return ['vapidPrivateKey'];
	}

}
