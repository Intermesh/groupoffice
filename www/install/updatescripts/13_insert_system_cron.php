<?php

$cron = new \GO\Base\Cron\CronJob();

$cron->name = 'Email Reminders';
$cron->active = true;
$cron->runonce = false;
$cron->minutes = '0,5,10,15,20,25,30,35,40,45,50,55';
$cron->hours = '*';
$cron->monthdays = '*';
$cron->months = '*';
$cron->weekdays = '*';
$cron->job = 'GO\Base\Cron\EmailReminders';

$cron->save();