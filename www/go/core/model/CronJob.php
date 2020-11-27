<?php

namespace go\core\model;

abstract class CronJob {
	abstract public function run(\go\core\model\CronJobSchedule $schedule);

	/**
	 * Install a cron job
	 *
	 * @param string $expression for example "0 0 * * *"
	 * @param bool $replace Replace if an existing job is found
	 * @return static
	 * @throws \Exception
	 */
	public static function install($expression, $replace = false) {

		$module = Module::findByClass(static::class);
		if(!$module) {
			throw new \Exception("Couldn't find module for ". static::class .' installation');
		}

		$name = substr( static::class, strrpos(static::class, '\\') + 1);

		if(!$replace || ($cron = CronJobSchedule::find()->where(['moduleId' => $module->id, 'name' => $name])->single())) {
			$cron = new CronJobSchedule();
		}

		$cron->moduleId = $module->id;
		$cron->name = $cron->description = $name;
		$cron->expression = $expression;

		if(!$cron->save()) {
			throw new \Exception("Couldn't save cronjob " . $cron->getValidationErrorsAsString());
		}

		return $cron;

	}
}