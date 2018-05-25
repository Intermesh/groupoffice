<?php
$cron = new \GO\Base\Cron\CronJob();
		
$cron->name = 'Calendar publisher';
$cron->active = true;
$cron->runonce = false;
$cron->minutes = '0';
$cron->hours = '*';
$cron->monthdays = '*';
$cron->months = '*';
$cron->weekdays = '*';
$cron->job = 'GO\Calendar\Cron\CalendarPublisher';

$cron->save();
