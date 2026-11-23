<?php

namespace GO\Files\Cron;

use go\core\model\LegacyCronJob;

/**
 * Notifies users of changes in the folders they follow
 *
 * Runs the old job GO\Files\Cron\NotifyFolderChanges in the new scheduler.
 */
final class NotifyFolderChangesJob extends LegacyCronJob
{
	protected static function legacyClass(): string
	{
		return \GO\Files\Cron\NotifyFolderChanges::class;
	}
}
