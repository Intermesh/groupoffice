<?php

namespace go\modules\community\dev\model;

use FilesystemIterator;
use go\core\Environment;
use go\core\Language;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Finds the translatable strings in the source code and decides in which language file they belong.
 *
 * Strings are found in:
 *
 * - ExtJS (.js): t("Text"), or t("Text", "module", "package") for another module
 * - GOUI (.ts that imports t): t("Text"), or t("Text", "package", "module") for another module
 * - PHP: go()->t("Text", "package", "module") and legacy GO::t("Text", "module", "package")
 *
 * Translations fall back from the module to core. So a string goes to the core language file when core uses it, when
 * two or more modules use it, or when the Dutch core file already translates it. Otherwise it goes to the module.
 *
 * @example
 * ```
 * $scanner = new LanguageScanner(['agya', 'udo']);
 * foreach ($scanner->getStrings() as $target => $strings) { ... } // target is "package/module"
 * ```
 */
class LanguageScanner
{
	/**
	 * Folders that don't contain source code to translate
	 */
	private const SKIP_FOLDERS = ['node_modules', 'vendor', 'dist', 'language', 'install', 'tests'];

	/**
	 * @var array<string, array<string, true>> string => ["package/module" => true]
	 */
	private array $uses = [];

	/**
	 * @var string[] Strings with a package or module that doesn't exist, like t("Text", "module", "package") in GOUI
	 *  where the arguments are swapped. Filled by {@see getStrings()}.
	 */
	public array $unknownTargets = [];

	/**
	 * @var array<string, string> "package/module: string" => the first file that uses it
	 */
	private array $files = [];

	/**
	 * @param string[] $skipPackages Packages to leave out, like the modules of customers. For legacy modules that are a
	 *  link to another folder, the name of that folder can be given too, like "duurzaamgebouw".
	 */
	public function __construct(private readonly array $skipPackages = [])
	{
	}

	/**
	 * The modules to scan
	 *
	 * @return array<int, array{string, string, string}> [package, module, folder]
	 */
	private function findModules(): array
	{
		$root = Environment::get()->getInstallPath();

		// GOUI and its Group-Office components fall back to the core translations
		$modules = [
			['core', 'core', $root . '/go/core'],
			['core', 'core', $root . '/views/Extjs3'],
			['core', 'core', $root . '/views/goui/groupoffice-core/script'],
			['core', 'core', $root . '/views/goui/goui/script'],
		];

		foreach (glob($root . '/go/modules/*', GLOB_ONLYDIR) as $pkgDir) {
			$pkg = basename($pkgDir);
			if (in_array($pkg, $this->skipPackages, true)) {
				continue;
			}
			foreach (glob($pkgDir . '/*', GLOB_ONLYDIR) as $modDir) {
				$modules[] = [$pkg, basename($modDir), $modDir];
			}
		}

		foreach (glob($root . '/modules/*') as $modDir) {
			if (!is_dir($modDir) || in_array('legacy', $this->skipPackages, true)) {
				continue;
			}
			// Legacy modules may link to another repository, like www/promodules or a customer's
			if (is_link($modDir) && in_array(basename(dirname(readlink($modDir))), $this->skipPackages, true)) {
				continue;
			}
			$modules[] = ['legacy', basename($modDir), realpath($modDir)];
		}

		return $modules;
	}

	/**
	 * @return string[]
	 */
	private function findSourceFiles(string $dir): array
	{
		$files = [];
		$it = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			fn(\SplFileInfo $f) => !in_array($f->getFilename(), self::SKIP_FOLDERS, true)
		));
		foreach ($it as $f) {
			$name = $f->getFilename();
			if (preg_match('/\.(js|ts|php)$/', $name) && !str_ends_with($name, '.min.js') && !str_ends_with($name, '.d.ts')) {
				$files[] = $f->getPathname();
			}
		}
		return $files;
	}

	private static function unquote(string $s, string $quote): string
	{
		return str_replace(['\\' . $quote, '\\\\'], [$quote, '\\'], $s);
	}

	private function add(string $str, string $package, string $module, string $file): void
	{
		// Strings with variables can't be translated
		if ($str === '' || str_contains($str, '$')) {
			return;
		}
		$this->uses[$str][$package . '/' . $module] = true;
		$this->files[$package . '/' . $module . ': ' . $str] ??= $file;
	}

	private function scanFile(string $file, string $package, string $module): void
	{
		$src = file_get_contents($file);

		if (str_ends_with($file, '.php')) {
			// go()->t("Text", "package", "module")
			preg_match_all('/go\(\)->t\(\s*(["\'])((?:\\\\.|(?!\1).)*)\1\s*(?:,\s*(["\'])(\w*)\3\s*(?:,\s*(["\'])(\w*)\5)?)?\s*\)/s', $src, $m, PREG_SET_ORDER);
			foreach ($m as $x) {
				$pkg = ($x[4] ?? '') ?: 'core';
				$mod = ($x[6] ?? '') ?: (($x[4] ?? '') ? $module : 'core');
				$this->add(self::unquote($x[2], $x[1]), $pkg, $mod, $file);
			}

			// GO::t("Text", "module", "package")
			preg_match_all('/GO::t\(\s*(["\'])((?:\\\\.|(?!\1).)*)\1\s*(?:,\s*(["\'])(\w*)\3\s*(?:,\s*(["\'])(\w*)\5)?)?\s*\)/s', $src, $m, PREG_SET_ORDER);
			foreach ($m as $x) {
				$mod = ($x[4] ?? '') ?: 'core';
				$pkg = ($x[6] ?? '') ?: ($mod === 'core' ? 'core' : 'legacy');
				$this->add(self::unquote($x[2], $x[1]), $pkg, $mod, $file);
			}
			return;
		}

		// t("Text") in this module, or another module: t("Text", "module", "package") in ExtJS and t("Text", "package",
		// "module") in GOUI
		// Some .ts files contain ExtJS code that uses the global t(). GOUI code imports t().
		$goui = str_ends_with($file, '.ts') && preg_match('/import\s*\{[^}]*\bt\b[^}]*\}\s*from/', $src);
		preg_match_all('/[\s\+\(\[:=,!]t\s*\(\s*(["\'])((?:\\\\.|(?!\1).)*?)\1\s*(?:,\s*(["\'])(\w*)\3\s*(?:,\s*(["\'])(\w*)\5)?)?\s*[\),]/', $src, $m, PREG_SET_ORDER);
		foreach ($m as $x) {
			if (!empty($x[4]) && $goui) {
				$pkg = $x[4];
				$mod = ($x[6] ?? '') ?: ($pkg === 'core' ? 'core' : $module);
			} else if (!empty($x[4])) {
				$mod = $x[4];
				$pkg = ($x[6] ?? '') ?: ($mod === 'core' ? 'core' : 'legacy');
			} else {
				[$pkg, $mod] = [$package, $module];
			}
			$this->add(self::unquote($x[2], $x[1]), $pkg, $mod, $file);
		}
	}

	/**
	 * Get the strings per language file
	 *
	 * @return array<string, string[]> "package/module" => strings, sorted by target
	 */
	public function getStrings(): array
	{
		$this->uses = [];
		$this->unknownTargets = [];
		$this->files = [];
		$modules = $this->findModules();
		foreach ($modules as [$package, $module, $dir]) {
			foreach ($this->findSourceFiles($dir) as $file) {
				$this->scanFile($file, $package, $module);
			}
		}

		$known = ['core/core' => true];
		foreach ($modules as [$package, $module]) {
			$known[$package . '/' . $module] = true;
		}

		// The Dutch core file is the reference for what core translates
		$nlCore = (new Language())->getFileTranslations('nl');

		$result = [];
		foreach ($this->uses as $str => $targets) {
			foreach (array_keys($targets) as $t) {
				if (!isset($known[$t])) {
					$this->unknownTargets[] = $t . ': ' . $str . ' (' . $this->files[$t . ': ' . $str] . ')';
				}
			}
			// The translation of an unknown module falls back to core
			$targets = array_values(array_unique(array_map(fn($t) => isset($known[$t]) ? $t : 'core/core', array_keys($targets))));
			$moduleTargets = array_values(array_filter($targets, fn($t) => $t !== 'core/core'));
			$toCore = in_array('core/core', $targets, true) || count($moduleTargets) > 1 || isset($nlCore[$str]);

			foreach ($toCore ? ['core/core'] : $moduleTargets as $target) {
				$result[$target][] = (string) $str;
			}
		}

		ksort($result);
		return $result;
	}
}
