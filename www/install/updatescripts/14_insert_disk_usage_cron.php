<?php
$cron = new \GO\Base\Cron\CronJob();
		
$cron->name = 'Calculate disk usage';
$cron->active = true;
$cron->runonce = false;
$cron->minutes = '0';
$cron->hours = '0';
$cron->monthdays = '*';
$cron->months = '*';
$cron->weekdays = '*';
$cron->job = 'GO\Base\Cron\CalculateDiskUsage';

$cron->save();