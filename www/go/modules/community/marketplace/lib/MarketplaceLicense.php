<?php

namespace go\modules\community\marketplace\lib;

use go\modules\community\marketplace\model\Repository;

/**
 * Runtime license gate for paid modules. A paid module overrides isLicensed():
 *
 *   public function isLicensed(): bool {
 *       return class_exists(\go\modules\community\marketplace\lib\MarketplaceLicense::class)
 *           && \go\modules\community\marketplace\lib\MarketplaceLicense::has('sf', 'chat');
 *   }
 *
 * Fully offline: verifies the cached JWT with the repository's pinned public
 * key. Returns false if marketplace isn't installed / no repo / no license.
 */
class MarketplaceLicense
{
    /**
     * @var array<string, \go\modules\community\marketplace\lib\LicenseVerifier|null> per-package verifier cache
     */
    private static array $verifiers = [];

    public static function has(string $package, string $module): bool
    {
        $verifier = self::verifierFor($package);
        return $verifier !== null && $verifier->has($package, $module);
    }

    private static function verifierFor(string $package): ?LicenseVerifier
    {
        if (array_key_exists($package, self::$verifiers)) {
            return self::$verifiers[$package];
        }
        $verifier = null;
        try {
            // Looked up by the server's package (the install target), never by the
            // repository's display name.
            $repo = Repository::find()->where(['package' => $package])->single();
            if ($repo && $repo->hasLicense()) {
                $verifier = $repo->licenseVerifier(LicenseHost::current());
            }
        } catch (\Throwable $e) {
            $verifier = null;
        }
        self::$verifiers[$package] = $verifier;
        return $verifier;
    }
}
