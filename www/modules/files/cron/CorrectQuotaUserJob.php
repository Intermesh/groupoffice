<?php

namespace GO\Files\Cron;

use go\core\model\LegacyCronJob;

/**
 * Corrects the quota user of the files
 *
 * Runs the old job GO\Files\Cron\CorrectQuotaUser in the new scheduler.
 */
final class CorrectQuotaUserJob extends LegacyCronJob
{
	protected static function legacyClass(): string
	{
		return \GO\Files\Cron\CorrectQuotaUser::class;
	}
}
