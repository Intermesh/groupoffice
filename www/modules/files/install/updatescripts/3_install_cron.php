<?php
$cron = new \GO\Base\Cron\CronJob();
		
$cron->name = 'Recalculate user quota';
$cron->active = true;
$cron->runonce = true;
$cron->minutes = '24';
$cron->hours = '2';
$cron->monthdays = '*';
$cron->months = '*';
$cron->weekdays = '*';
$cron->job = 'GO\Files\Cron\RecalculateDiskUsage';

$cron->save();
