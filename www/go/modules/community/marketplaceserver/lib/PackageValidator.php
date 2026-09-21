<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Validates a release ZIP's structure at PUBLISH time, server-side — the mirror
 * of the client's {@see \go\modules\community\marketplace\lib\PackageValidator},
 * so a broken/mis-rooted archive is refused when a manager uploads it instead of
 * only failing much later on the customer's machine during extraction. Pure — it
 * takes the archive's entry names + the expected module name and returns an error
 * string, or null when the archive is safe.
 */
class PackageValidator
{
    /**
     * @param array<string> $entryNames archive entry names (files and dirs)
     * @param string $module the expected single top-level directory
     * @return string|null null = safe; otherwise a human-readable reason
     */
    public static function validateEntries(array $entryNames, string $module): ?string
    {
        if (!preg_match('/^[a-z0-9_]+$/i', $module)) {
            return 'Unsafe module name';
        }
        if (count($entryNames) === 0) {
            return 'The package archive is empty';
        }
        $prefix = $module . '/';
        $hasContent = false;
        foreach ($entryNames as $name) {
            $normalized = str_replace('\\', '/', $name);
            if ($normalized === '') {
                continue;
            }
            if ($normalized[0] === '/' || preg_match('#^[a-zA-Z]:#', $normalized)) {
                return 'Absolute path in archive: ' . $name;
            }
            if (strpos($normalized, '../') !== false || str_ends_with($normalized, '/..') || $normalized === '..') {
                return 'Path traversal in archive: ' . $name;
            }
            if ($normalized !== $module && strpos($normalized, $prefix) !== 0) {
                return 'Entry "' . $name . '" is outside the expected module root "' . $module
                    . '/". A release ZIP must contain exactly one top-level folder named "' . $module
                    . '" (this release\'s module name, case-sensitive) with the module files inside it.';
            }
            $hasContent = true;
        }
        if (!$hasContent) {
            return 'The package archive contains no files under "' . $module . '/"';
        }
        return null;
    }

    /**
     * Open the ZIP at $path and validate its entries against $module. Returns an
     * error string (unopenable / unsafe) or null when the archive is a safe,
     * single-rooted module package.
     *
     * @param string $path filesystem path to the ZIP
     * @param string $module expected module (top-level folder) name
     * @return string|null null = safe; otherwise a human-readable reason
     */
    public static function validateZipFile(string $path, string $module): ?string
    {
        if (!is_file($path)) {
            return 'The uploaded package file could not be read';
        }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return 'The uploaded file is not a valid ZIP archive';
        }
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if ($entry !== false) {
                $names[] = $entry;
            }
        }
        $zip->close();
        return self::validateEntries($names, $module);
    }
}
