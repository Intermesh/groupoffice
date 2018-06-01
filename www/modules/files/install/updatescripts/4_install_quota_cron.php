<?php
$cron = new \GO\Base\Cron\CronJob();
		
$cron->name = 'Correct Quota User';
$cron->active = true;
$cron->runonce = true;
$cron->minutes = '0';
$cron->hours = '2';
$cron->monthdays = '*';
$cron->months = '*';
$cron->weekdays = '*';
$cron->job = 'GO\Files\Cron\CorrectQuotaUser';

$cron->save();
