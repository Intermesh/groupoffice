<?php

namespace GO\Servermanager;


class ServerManagerModule extends \GO\Base\Module{
	public function install() {
			
		parent::install();
		
		$cron = new \GO\Base\Cron\CronJob();
		
		$cron->name = 'Subcron';
		$cron->active = true;
		$cron->runonce = false;
		$cron->minutes = '*';
		$cron->hours = '*';
		$cron->monthdays = '*';
		$cron->months = '*';
		$cron->weekdays = '*';
		$cron->job = 'GO\Servermanager\Cron\SubCron';
		
		$cron->save();
	}
}