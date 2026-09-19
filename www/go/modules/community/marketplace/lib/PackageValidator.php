<?php

namespace go\modules\community\marketplace\lib;

/**
 * Validates a downloaded module ZIP's entry names before extraction. Pure —
 * takes the list of archive entry names and the expected module name, returns
 * an error string or null if safe. Defends against path traversal, absolute
 * paths, and multi/wrong-root archives.
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
            return 'Empty archive';
        }
        $prefix = $module . '/';
        foreach ($entryNames as $name) {
            $normalized = str_replace('\\', '/', $name);
            if ($normalized === '' ) {
                continue;
            }
            if ($normalized[0] === '/' || preg_match('#^[a-zA-Z]:#', $normalized)) {
                return 'Absolute path in archive: ' . $name;
            }
            if (strpos($normalized, '../') !== false || str_ends_with($normalized, '/..') || $normalized === '..') {
                return 'Path traversal in archive: ' . $name;
            }
            // every entry must live under {module}/
            if ($normalized !== $module && strpos($normalized, $prefix) !== 0) {
                return 'Entry "' . $name . '" is outside the expected module root "' . $module . '/". '
                    . 'A release ZIP must contain exactly one top-level folder named "' . $module . '" '
                    . '(this release\'s module name, case-sensitive) with the module files inside it. '
                    . 'Top-level entries found in this archive: ' . implode(', ', self::topLevelEntries($entryNames)) . '.';
            }
        }
        return null;
    }

    /**
     * The distinct first path segments of the archive entries — for a helpful
     * "what's actually in here vs. what we expected" error message.
     *
     * @param array<string> $entryNames
     * @return array<string>
     */
    private static function topLevelEntries(array $entryNames): array
    {
        $top = [];
        foreach ($entryNames as $name) {
            $n = ltrim(str_replace('\\', '/', $name), '/');
            if ($n === '') {
                continue;
            }
            $seg = explode('/', $n)[0];
            $top[$seg . '/'] = true;
        }
        return array_keys($top);
    }
}
