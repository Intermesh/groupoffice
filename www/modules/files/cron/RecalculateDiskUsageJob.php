<?php

namespace GO\Files\Cron;

use go\core\model\LegacyCronJob;

/**
 * Recalculates the disk usage of the users
 *
 * Runs the old job GO\Files\Cron\RecalculateDiskUsage in the new scheduler.
 */
final class RecalculateDiskUsageJob extends LegacyCronJob
{
	protected static function legacyClass(): string
	{
		return \GO\Files\Cron\RecalculateDiskUsage::class;
	}
}
