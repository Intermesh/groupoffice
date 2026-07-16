<?php

namespace go\modules\community\marketplace\cron;

use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\core\http\Request;
use go\core\ErrorHandler;
use go\modules\community\marketplace\lib\ApiClient;
use go\modules\community\marketplace\model\Repository;

/**
 * Daily: refresh each repository's license JWT. On network failure keep the
 * cached JWT (modules stay licensed until their per-module expiry). Key-rotation
 * mismatches are flagged, not silently re-pinned.
 */
class RefreshLicenses extends CronJob
{
    /**
     * @param \go\core\model\CronJobSchedule $schedule
     * @return void
     */
    public function run(CronJobSchedule $schedule): void
    {
        // getHost() resolves under CLI too (parses the configured URL) — no
        // special-casing, no gethostname() fallback needed.
        $host = Request::get()->getHost();

        foreach (Repository::find() as $repo) {
            try {
                $client = new ApiClient($repo);
                $info = $client->info();
                if (!empty($repo->publicKey) && ($info['publicKey'] ?? '') !== $repo->publicKey) {
                    $repo->keyMismatch = true;
                    $repo->lastError = 'Signing key changed';
                    $repo->save();
                    continue;                                  // do not refresh license under a changed key
                }
                $repo->licenseJwt = $client->license((string) $host);
                $repo->lastSyncAt = new \go\core\util\DateTime();
                $repo->lastError = null;
                $repo->save();
            } catch (\Throwable $e) {
                $repo->lastError = $e->getMessage();
                $repo->save();                                 // keep cached licenseJwt
                ErrorHandler::logException($e);
            }
        }
    }
}
