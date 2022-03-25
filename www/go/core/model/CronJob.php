<?php

namespace go\core\model;

use Exception;

abstract class CronJob {
	abstract public function run(CronJobSchedule $schedule);

	/**
	 * Install a cron job
	 *
	 * @param string $expression for example "0 0 * * *"
	 * @param bool $replace Replace if an existing job is found
	 * @return CronJobSchedule
	 * @throws Exception
	 */
	public static function install(string $expression, bool $replace = false): CronJobSchedule
	{
		$module = Module::findByClass(static::class);
		$name = substr( static::class, strrpos(static::class, '\\') + 1);

		if(!$replace || (!$cron = CronJobSchedule::find()->where(['moduleId' => $module->id, 'name' => $name])->single())) {
			$cron = new CronJobSchedule();
		}

		$cron->moduleId = $module->id;
		$cron->name = $cron->description = $name;
		$cron->expression = $expression;
		$cron->enabled = true;

		if(!$cron->save()) {
			throw new Exception("Couldn't save cronjob " . $cron->getValidationErrorsAsString());
		}

		return $cron;

	}
}