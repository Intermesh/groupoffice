<?php
/**
 * Includes the PHPStan baselines: the errors that existed before PHPStan ran in CI. Only new errors are reported.
 *
 * phpstan-baseline.neon covers the files of this repository. Modules in their own repository, like
 * www/go/modules/business, aren't part of it, so their errors can't be in this baseline. Such a module can add its own
 * phpstan-baseline.neon in its root folder, which is included here when it exists.
 *
 * Regenerate the baseline of this repository in a clean checkout, without the modules of other repositories, like CI
 * does. Otherwise it contains errors of those modules.
 */

$includes = [__DIR__ . '/phpstan-baseline.neon'];

foreach (glob(__DIR__ . '/../www/go/modules/*/phpstan-baseline.neon') ?: [] as $file) {
	$includes[] = $file;
}

return ['includes' => $includes];
