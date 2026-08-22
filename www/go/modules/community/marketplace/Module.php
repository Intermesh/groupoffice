<?php

namespace go\modules\community\marketplace;

use go\core;
use go\core\model;
use go\modules\community\marketplace\model\Settings;

class Module extends core\Module
{
    /**
     * Static token this client build sends in the `X-Marketplace-Client` header
     * when self-registering against a marketplace server. It is NOT a secret
     * (it ships in every client) — it only lets the server reject requests that
     * did not come from a genuine client build. The server accepts this value
     * (see Settings::DEFAULT_CLIENT_TOKEN), so an admin only has to tick
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
     * @return core\Settings|Settings|null
     */
    public function getSettings(): ?\go\core\Settings
    {
        return Settings::get();
    }

    /**
     * Register the daily license-refresh cron on fresh installs. Existing
     * installs pick this up via install/updates.php (afterInstall only runs
     * once, at install time).
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
        // Every 4 hours: a server-side revocation (an entitlement's revokedAt, or a
        // lapsed expiry) then reaches this client within hours instead of a full
        // day, while the runtime license gate stays 100% offline. On a network
        // failure the cached JWT keeps modules licensed until their own expiry.
        $cron->expression = "0 */4 * * *";
        $cron->description = go()->t("Refresh marketplace licenses", 'community', 'marketplace');
        $cron->enabled = true;
        if (!$cron->save()) {
            throw new \Exception("Failed to save cron: " . var_export($cron->getValidationErrors(), true));
        }
        return parent::afterInstall($model);
    }
}