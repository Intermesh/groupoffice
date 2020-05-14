<?php

namespace GO\Mail2project;


use GO\Mail2project\Cron\ImportImap;

class Mail2projectModule extends \GO\Base\Module
{
    /**
     * @return bool
     */
    public function autoInstall()
    {
        return true;
    }

    /**
     * @return string
     */
    public function package()
    {
        return 'Nuw';
    }

    /**
     * @return string
     */
    public function author()
    {
        return 'Michal Charvát';
    }

    /**
     * @return String
     */
    public function authorEmail()
    {
        return 'info@michalcharvat.cz';
    }

    /**
     * @return array
     */
    public function depends()
    {
        return array('email', 'projects2');
    }

    /**
     * @return bool|void
     * @throws \GO\Base\Exception\AccessDenied
     * @throws \Exception
     */
    public function install()
    {
        parent::install();

        $cron = new \GO\Base\Cron\CronJob();

        $cron->name = 'Import projects from mailbox';
        $cron->active = false;
        $cron->runonce = false;
        $cron->minutes = '*/15';
        $cron->hours = '*';
        $cron->monthdays = '*';
        $cron->months = '*';
        $cron->weekdays = '*';
        $cron->job = ImportImap::class;

        $cron->save();
    }
}
