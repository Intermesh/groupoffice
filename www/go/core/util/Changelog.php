<?php

namespace go\core\util;

use go\core\Environment;

/**
 * Reads the changes per release from CHANGELOG.md
 *
 * A release starts with a line "dd-mm-yyyy: <version>" and is followed by its changes, which start with "- ". Lines
 * that are indented continue the change above them. Unreleased changes above the first release are left out.
 *
 * ```
 * $releases = Changelog::load()?->between("26.0.60", go()->getVersion());
 * ```
 */
class Changelog
{
	/**
	 * Releases newest first, as in the file
	 *
	 * @var array{version: string, date: string, changes: string[]}[]
	 */
	private array $releases;

	public function __construct(string $text)
	{
		$this->releases = $this->parse($text);
	}

	/**
	 * Loads the changelog of this installation
	 *
	 * The release build puts it in changelog.php in the install folder, which returns the text. The web server serves
	 * that folder and the changelog shows the exact version, so it isn't a .md file there. In a source checkout
	 * CHANGELOG.md is in the folder above.
	 *
	 * @return self|null null when there is no changelog
	 */
	public static function load(): ?self
	{
		$installPath = Environment::get()->getInstallPath();

		if (is_file($installPath . '/changelog.php')) {
			$text = require $installPath . '/changelog.php';
			return is_string($text) ? new self($text) : null;
		}

		$path = dirname($installPath) . '/CHANGELOG.md';
		return is_file($path) ? new self(file_get_contents($path)) : null;
	}

	/**
	 * All releases, newest first
	 *
	 * @return array{version: string, date: string, changes: string[]}[]
	 */
	public function getReleases(): array
	{
		return $this->releases;
	}

	/**
	 * The releases newer than $from up to and including $to, newest first
	 *
	 * @param string $from The version the user had, like "26.0.60"
	 * @param string $to The current version
	 * @return array{version: string, date: string, changes: string[]}[]
	 */
	public function between(string $from, string $to): array
	{
		return array_values(array_filter($this->releases, function (array $release) use ($from, $to) {
			return version_compare($release['version'], $from, '>') && version_compare($release['version'], $to, '<=');
		}));
	}

	private function parse(string $text): array
	{
		$releases = [];
		$current = null;

		foreach (preg_split('/\r?\n/', $text) as $line) {
			if (preg_match('/^\s*(\d{2}-\d{2}-\d{4}):\s*(\d+(?:\.\d+)+)\s*$/', $line, $matches)) {
				if (isset($current)) {
					$releases[] = $current;
				}
				$current = ['version' => $matches[2], 'date' => $matches[1], 'changes' => []];
				continue;
			}

			if (!isset($current) || preg_match('/^(<{7}|={7}|>{7})/', $line)) {
				// unreleased changes and leftover merge markers
				continue;
			}

			if (str_starts_with($line, '- ')) {
				$current['changes'][] = trim(substr($line, 2));
			} elseif (preg_match('/^\s+\S/', $line) && !empty($current['changes'])) {
				$last = count($current['changes']) - 1;
				$current['changes'][$last] .= ' ' . trim($line);
			}
		}

		if (isset($current)) {
			$releases[] = $current;
		}

		return $releases;
	}
}
