<?php

namespace go\core\model;

use Cron\CronExpression;
use Exception;
use GO\Base\Cron\AbstractCron;
use go\core\ErrorHandler;

/**
 * Runs a job of the old cron system in the new scheduler
 *
 * Since 26.1 the go_cron jobs are no longer run. Each old job (a GO\Base\Cron\AbstractCron class) gets a small
 * subclass of this class that names it in legacyClass(). The subclass is found by its name like every other CronJob,
 * so it lives in the same namespace as the old job and needs another name, like "DeleteExpiredLinksJob".
 *
 * Old jobs that work with the users and groups of their go_cron job can't be wrapped like this.
 */
abstract class LegacyCronJob extends CronJob
{
	/**
	 * The old job class this job runs
	 *
	 * @return class-string<AbstractCron>
	 */
	abstract protected static function legacyClass(): string;

	/**
	 * Runs the old job with an empty go_cron job
	 *
	 * @throws Exception
	 */
	public function run(CronJobSchedule $schedule): void
	{
		$cls = static::legacyClass();
		$job = new $cls();

		// The jobs that are wrapped don't use their go_cron job
		$job->run(new \GO\Base\Cron\CronJob());
	}

	/**
	 * Install the schedule, with the label of the old job as description
	 *
	 * @param string $expression for example "0 0 * * *"
	 * @param bool $replace Replace if an existing job is found
	 * @throws Exception
	 */
	public static function install(string $expression, bool $replace = false): CronJobSchedule
	{
		$schedule = parent::install($expression, $replace);
		self::describe($schedule);
		return $schedule;
	}

	/**
	 * Create the schedule from the old go_cron job of the old class, for an upgrade
	 *
	 * The minutes, hours, days, months and weekdays of the old job become the expression. Old jobs were switched off by
	 * an update, so the new job is enabled when an old job exists. Nothing is created when there's no old job, or when
	 * the new job is scheduled already. Old jobs that ran only once are left out.
	 *
	 * An old schedule that isn't a valid expression is logged and left out.
	 *
	 * @return CronJobSchedule|null The new schedule, or null when none was created
	 * @throws Exception
	 */
	public static function migrateLegacySchedule(): ?CronJobSchedule
	{
		$module = Module::findByClass(static::class);
		$name = substr(static::class, strrpos(static::class, '\\') + 1);
		if (CronJobSchedule::find()->where(['moduleId' => $module->id, 'name' => $name])->single()) {
			return null;
		}

		if (!go()->getDatabase()->hasTable('go_cron')) {
			return null;
		}

		$old = go()->getDbConnection()
			->select('minutes, hours, monthdays, months, weekdays')
			->from('go_cron')
			->where('job', '=', static::legacyClass())
			->andWhere('runonce', '=', 0)
			->andWhere('autodestroy', '=', 0)
			->orderBy(['id' => 'ASC'])
			->single();

		if (!$old) {
			return null;
		}

		$expression = implode(' ', [$old['minutes'], $old['hours'], $old['monthdays'], $old['months'], $old['weekdays']]);
		if (!CronExpression::isValidExpression($expression)) {
			// Don't stop the upgrade. The administrator can schedule the job in the system settings.
			ErrorHandler::log("The schedule '" . $expression . "' of the old cron job " . static::legacyClass() . " is not valid, so it isn't scheduled");
			return null;
		}

		return static::install($expression);
	}

	private static function describe(CronJobSchedule $schedule): void
	{
		$cls = static::legacyClass();
		$label = (new $cls())->getLabel();
		if (!empty($label) && $schedule->description !== $label) {
			$schedule->description = $label;
			if (!$schedule->save()) {
				throw new Exception("Couldn't save cronjob " . $schedule->getValidationErrorsAsString());
			}
		}
	}
}
