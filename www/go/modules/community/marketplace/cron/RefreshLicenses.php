<?php

namespace go\modules\community\marketplace\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\core\ErrorHandler;
use go\modules\community\marketplace\lib\ApiClient;
use go\modules\community\marketplace\lib\LicenseHost;
use go\modules\community\marketplace\model\Repository;

/**
 * Daily: refresh each repository's license JWT. The token is valid for 14 days,
 * so a failed refresh keeps the cached one working until then. Key-rotation
 * mismatches are flagged, not silently re-pinned. One repository failing never
 * stops the others.
 */
class RefreshLicenses extends CronJob
{
    /**
     * @param \go\core\model\CronJobSchedule $schedule
     * @return void
     */
    public function run(CronJobSchedule $schedule): void
    {
        // The same host the web refresh and the runtime gate use.
        $host = LicenseHost::current();

        foreach (Repository::find() as $repo) {
            try {
                $client = new ApiClient($repo);
                $info = $client->info();
                if (($info['publicKey'] ?? '') !== $repo->pinnedPublicKey()) {
                    $repo->flagKeyMismatch();                  // do not refresh license under a changed key
                } else {
                    $repo->storeLicense($client->license($host), $host);
                }
            } catch (\Throwable $e) {
                $repo->setLastError($e->getMessage());         // keep cached licenseJwt
                ErrorHandler::logException($e);
            }

            try {
                if (!$repo->save()) {
                    ErrorHandler::log('Marketplace: could not save repository ' . $repo->id . ': ' . $repo->getValidationErrorsAsString());
                }
            } catch (\Throwable $e) {
                ErrorHandler::logException($e);
            }
        }
    }
}
