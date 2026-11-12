<?php
/**
 * Customer-side assertions that only exist inside the instance: what the
 * runtime license gate says, and what the repository actually cached.
 *
 * Usage: php probe-client.php <package> <moduleName>
 * Prints one JSON object.
 */

use go\core\App;
use go\modules\community\marketplace\lib\LicenseHost;
use go\modules\community\marketplace\lib\MarketplaceLicense;
use go\modules\community\marketplace\model\Repository;

// These ship inside the module, and Group-Office serves its own tree with no
// .htaccess — so this file is reachable over HTTP on any install that has the
// module. customer.php can DELETE an account; seed-server.php writes products.
// CLI only, explicitly, rather than relying on the hardcoded autoload path
// happening to be wrong everywhere else.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require '/var/www/html/vendor/autoload.php';
App::get();

$package = $argv[1] ?? 'mpe2e';
$moduleName = $argv[2] ?? 'mpdemo';

$repo = Repository::find()->where(['package' => $package])->single();
$downloaded = [];
foreach (($repo->downloadedModules ?? []) as $dm) {
    $downloaded[$dm->moduleName] = $dm->version;
}

echo json_encode([
    'host' => LicenseHost::current(),
    'repo' => $repo ? ['id' => $repo->id, 'name' => $repo->name, 'package' => $repo->getPackage(),
        'hasLicense' => $repo->hasLicense(), 'keyMismatch' => $repo->getKeyMismatch(),
        'lastError' => $repo->lastError] : null,
    'downloaded' => $downloaded,
    // The gate a paid module's isLicensed() calls. Fully offline: cached JWT +
    // pinned key, no network.
    'licensed' => MarketplaceLicense::has($package, $moduleName),
]) . "\n";
