<?php
/**
 * Rebuilds the cache like an installation where the business modules can't be loaded, for {@see \go\core\event\ListenersTest}.
 *
 * A release build encodes every file of a business module except Module.php. Without a SourceGuardian loader the
 * business modules aren't available and loading one of their other files ends PHP. This simulates that before the
 * bootstrap runs, so nothing has loaded those classes yet:
 *
 * - "allowed_modules" leaves out business/*, so {@see \go\core\Module::isAvailable()} is false. Without a loader that's
 *   because {@see \go\core\Module::isLicensed()} fails, but the development container has a loader.
 * - Loading a business class other than Module throws.
 *
 * The bootstrap then calls go()->rebuildCache(). Prints "OK" when that worked.
 */

require __DIR__ . '/../../../../../www/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
	if (str_starts_with($class, 'go\\modules\\business\\') && !str_ends_with($class, '\\Module')) {
		throw new RuntimeException("Loaded encoded class $class of an unavailable business module");
	}
}, true, true);

(new ReflectionProperty(\go\core\Module::class, 'allowedModules'))->setValue(null, ['core/*', 'community/*', 'legacy/*']);

ob_start();
require __DIR__ . '/../../../../bootstrap-none.php';
ob_end_clean();

echo "OK";
