<?php
/**
 * Regenerates the PHPStan baseline of a module folder in its own repository, like www/go/modules/business. See
 * tests/phpstan-baselines.php.
 *
 * Analyses all code and keeps the errors in files of the module folder, because errors of core traits that a module
 * class uses are only reported when the core is analysed too. Run it in the container from www:
 *
 *   docker compose exec -u www-data groupoffice php ../scripts/phpstan-module-baseline.php business
 *
 * Errors in the core that aren't in tests/phpstan-baseline.neon are not included. Fix them or regenerate that baseline
 * with scripts/phpstan-baseline.sh first.
 */

if ($argc !== 2 || !preg_match('/^[a-z0-9_]+$/', $argv[1])) {
	fwrite(STDERR, "Usage: php ../scripts/phpstan-module-baseline.php <folder in www/go/modules>\n");
	exit(1);
}

$module = $argv[1];
$moduleDir = realpath(__DIR__ . '/../www/go/modules/' . $module);
if ($moduleDir === false) {
	fwrite(STDERR, "Module folder www/go/modules/$module doesn't exist\n");
	exit(1);
}

$www = realpath(__DIR__ . '/../www');
// In the module folder: PHPStan writes the paths relative to the baseline, which doesn't work from another root.
$all = $moduleDir . '/.phpstan-baseline-all-' . getmypid() . '.php';

try {
	// Without the baseline of the module itself, so its current errors are reported.
	putenv('GO_PHPSTAN_SKIP_BASELINE=' . $module);
	passthru(
		'cd ' . escapeshellarg($www) . ' && ./vendor/bin/phpstan analyse -c ../tests/phpstan.neon --memory-limit=2G'
		. ' --no-progress --generate-baseline ' . escapeshellarg($all),
		$status
	);
	if (!is_file($all)) {
		fwrite(STDERR, "PHPStan didn't generate a baseline\n");
		exit($status ?: 1);
	}

	$baseline = require $all;
} finally {
	@unlink($all);
}

$neon = "parameters:\n\tignoreErrors:\n";
$count = 0;
foreach ($baseline['parameters']['ignoreErrors'] as $error) {
	$path = realpath($error['path']);
	if ($path === false || !str_starts_with($path, $moduleDir . '/')) {
		continue;
	}

	// The format of the baselines that PHPStan generates
	$neon .= "\t\t-\n";
	foreach (['message', 'rawMessage'] as $key) {
		if (isset($error[$key])) {
			$neon .= "\t\t\t$key: '" . str_replace("'", "''", $error[$key]) . "'\n";
		}
	}
	if (isset($error['identifier'])) {
		$neon .= "\t\t\tidentifier: " . $error['identifier'] . "\n";
	}
	$neon .= "\t\t\tcount: " . $error['count'] . "\n";
	$neon .= "\t\t\tpath: " . substr($path, strlen($moduleDir) + 1) . "\n\n";
	$count += $error['count'];
}

if ($count === 0) {
	$neon = "parameters:\n\tignoreErrors: []\n";
}

file_put_contents($moduleDir . '/phpstan-baseline.neon', rtrim($neon) . "\n");
echo "Baseline of www/go/modules/$module generated with $count errors.\n";
