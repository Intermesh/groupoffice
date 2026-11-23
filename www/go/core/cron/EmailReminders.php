<?php

namespace go\core\cron;

use go\core\model\LegacyCronJob;

/**
 * Mails the reminders of users who chose to get them by e-mail
 *
 * Runs the old job GO\Base\Cron\EmailReminders in the new scheduler.
 */
final class EmailReminders extends LegacyCronJob
{
	protected static function legacyClass(): string
	{
		return \GO\Base\Cron\EmailReminders::class;
	}
}
