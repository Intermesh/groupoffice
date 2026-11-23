<?php

namespace GO\Files\Cron;

use go\core\model\LegacyCronJob;

/**
 * Deletes the files of expired download links that are set to be deleted
 *
 * Runs the old job GO\Files\Cron\DeleteExpiredLinks in the new scheduler.
 */
final class DeleteExpiredLinksJob extends LegacyCronJob
{
	protected static function legacyClass(): string
	{
		return \GO\Files\Cron\DeleteExpiredLinks::class;
	}
}
