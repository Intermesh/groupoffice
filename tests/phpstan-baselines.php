<?php
/**
 * Includes the PHPStan baselines: the errors that existed before PHPStan ran in CI. Only new errors are reported.
 *
 * phpstan-baseline.neon covers the files of this repository. Modules in their own repository, like
 * www/go/modules/business, aren't part of it, so their errors can't be in this baseline. Such a module can add its own
 * phpstan-baseline.neon in its root folder, which is included here when it exists.
 *
 * Regenerate the baseline of this repository with scripts/phpstan-baseline.sh and that of a module with
 * scripts/phpstan-module-baseline.php.
 */

$includes = [__DIR__ . '/phpstan-baseline.neon'];

// Set by scripts/phpstan-module-baseline.php: the baseline that is being regenerated.
$skip = getenv('GO_PHPSTAN_SKIP_BASELINE') ?: null;

foreach (glob(__DIR__ . '/../www/go/modules/*/phpstan-baseline.neon') ?: [] as $file) {
	if ($skip !== null && basename(dirname($file)) === $skip) {
		continue;
	}
	$includes[] = $file;
}

return ['includes' => $includes];
