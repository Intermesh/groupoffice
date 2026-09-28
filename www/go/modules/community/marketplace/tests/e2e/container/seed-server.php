<?php
/**
 * Seed the mp-e2e VENDOR instance: settings, one paid product and one release
 * whose package is a real, minimal GO module ZIP.
 *
 * Usage: php seed-server.php <package> <moduleName> <version>
 *
 * Prints nothing but "seeded" on success — the scenario drives everything else
 * through the public API, which is the point of the harness.
 */

use go\core\App;
use go\modules\community\marketplaceserver\model;

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
$version = $argv[3] ?? '1.0.0';

// The public page API runs unauthenticated; seeding creates entities, so it
// needs a real auth state (createdBy stamping, validation).
go()->setAuthState((new \go\core\auth\TemporaryState())->setUserId(1));

$settings = model\Settings::get();
$settings->packageName = $package;
$settings->registrationEnabled = true;
$settings->supportedGoBranches = go()->getMajorVersion();
if (!$settings->save()) {
    fwrite(STDERR, "settings: " . var_export($settings->getValidationErrors(), true) . "\n");
    exit(1);
}
$settings->ensureKeyPair();

$product = model\Product::find()->where(['moduleName' => $moduleName])->single() ?: new model\Product();
$product->type = model\Product::TYPE_MODULE;
$product->moduleName = $moduleName;
$product->title = 'MP e2e demo module';
$product->description = 'Published by the marketplace e2e harness.';
$product->price = 10.0;
$product->currency = 'EUR';
$product->active = true;
if (!$product->save()) {
    fwrite(STDERR, "product: " . $product->getValidationErrorsAsString() . "\n");
    exit(1);
}

// A real module ZIP: one top-level folder named after the module, which is
// exactly the shape PackageValidator insists on.
$zipPath = tempnam(sys_get_temp_dir(), 'mpe2e') . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "cannot create zip\n");
    exit(1);
}
$ns = 'go\\modules\\' . $package . '\\' . $moduleName;
$zip->addFromString($moduleName . '/Module.php', "<?php\n\nnamespace $ns;\n\nclass Module extends \\go\\core\\Module\n{\n    public function getAuthor(): string\n    {\n        return 'mp-e2e harness';\n    }\n}\n");
$zip->addFromString($moduleName . '/language/en.php', "<?php\n\nreturn [\n    'name' => 'MP e2e demo',\n    'description' => 'Delivered over the marketplace API.',\n];\n");
$zip->addFromString($moduleName . '/MARKER.txt', "mp-e2e $version\n");
$zip->close();

$blob = \go\core\fs\Blob::fromFile(new \go\core\fs\File($zipPath));
$blob->type = 'application/zip';
$blob->name = $moduleName . '-' . $version . '.zip';
if (!$blob->save()) {
    fwrite(STDERR, "blob: " . $blob->getValidationErrorsAsString() . "\n");
    exit(1);
}
unlink($zipPath);

$release = model\Release::find()
    ->where(['productId' => $product->id, 'version' => $version, 'goVersion' => go()->getMajorVersion()])
    ->single() ?: new model\Release();
$release->productId = $product->id;
$release->moduleName = $moduleName;
$release->version = $version;
$release->goVersion = go()->getMajorVersion();
$release->changelog = 'First e2e release.';
$release->blobId = $blob->id;
$release->publishedAt = new \go\core\util\DateTime();
$release->active = true;
if (!$release->save()) {
    fwrite(STDERR, "release: " . $release->getValidationErrorsAsString() . "\n");
    exit(1);
}

echo "seeded product={$product->id} release={$release->id} branch=" . go()->getMajorVersion() . "\n";
