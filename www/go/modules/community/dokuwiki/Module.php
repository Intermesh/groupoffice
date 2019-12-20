<?php

namespace go\modules\community\dokuwiki;

use go\modules\community\dokuwiki\model\Settings;

class Module extends \go\core\Module
{
    /**
     * Return the name of the author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Michal Charvat';
    }

    /**
     * Return the e-mail address of the author.
     *
     * @return string
     */
    public function getAuthorEmail()
    {
        return 'info@michalcharvat.cz';
    }

    /**
     * @return \go\modules\community\dokuwiki\model\Settings
     */
    public function getSettings()
    {
        return Settings::get();
    }
}
