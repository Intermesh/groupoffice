<?php

namespace go\modules\community\marketplace;

use go\core;
use go\core\model;

class Module extends core\Module
{
    /**
     * Static token this client build sends in the `X-Marketplace-Client` header
     * when self-registering against a marketplace server. It is NOT a secret
     * (it ships in every client) — it only lets the server reject requests that
     * did not come from a genuine client build. The server accepts this value
     * (see marketplaceserver's Settings::DEFAULT_CLIENT_TOKEN, which this value
     * must stay in sync with), so an admin only has to tick
     * "Allow self-registration".
     */
    const CLIENT_TOKEN = 'groupoffice-marketplace-client';

    /**
     * Return the name of the author.
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return 'Michal Charvat <info@michalcharvat.cz>';
    }

    /**
     * Register the daily license-refresh cron.
     *
     * @param model\Module $model
     * @return bool
     * @throws \Exception
     */
    protected function afterInstall(model\Module $model): bool
    {
        $cron = new model\CronJobSchedule();
        $cron->moduleId = $model->id;
        $cron->name = "RefreshLicenses";
        // Once a day, at a random time per instance so clients don't all hit the
        // server at once. The license JWT is valid for 14 days, so a server-side
        // revocation reaches this client within a day while the runtime gate
        // stays fully offline, and a network outage is survived for two weeks.
        $cron->expression = random_int(0, 59) . ' ' . random_int(0, 23) . ' * * *';
        $cron->description = go()->t("Refresh marketplace licenses", 'community', 'marketplace');
        $cron->enabled = true;
        if (!$cron->save()) {
            throw new \Exception("Failed to save cron: " . var_export($cron->getValidationErrors(), true));
        }
        return parent::afterInstall($model);
    }
}