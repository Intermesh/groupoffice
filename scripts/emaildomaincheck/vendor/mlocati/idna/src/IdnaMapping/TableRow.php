<?php

namespace MLocati\IDNA\IdnaMapping;

use Exception;

/**
 * Represents the data extracted from a IDNA Mapping Table row.
 */
class TableRow
{
    /**
     * One or two codepoints.
     *
     * @var int[]
     */
    public $range;

    /**
     * valid, mapped, deviation, ...
     *
     * @var string
     */
    public $status;

    /**
     * Hex value(s) of mapped, deviation, ....
     *
     * @var int[]|null
     */
    public $mapping;

    /**
     * Status for IDNA2008 (NV8, XV8, ...).
     *
     * @var string
     */
    public $statusIDNA2008;

    /**
     * The version from which this range is applicable.
     *
     * @var string
     */
    public $fromVersion;

    /**
     * Other comments.
     *
     * @var string
     */
    public $comment;

    /**
     * Initialize the instance.
     */
    protected function __construct()
    {
        $this->mapping = null;
        $this->statusIDNA2008 = '';
        $this->fromVersion = '';
    }

    /**
     * Parse a source line and returns the data found (or null if the line does not contain any data).
     *
     * @param string $line
     *
     * @throws \Exception
     *
     * @return static|null
     */
    public static function parse($line)
    {
        $result = null;
        $hashPosition = strpos($line, '#');
        $data = trim(($hashPosition === false) ? $line : substr($line, 0, $hashPosition));
        if ($data !== '') {
            $result = new static();
            $comment = ($hashPosition === false) ? '' : trim(substr($line, $hashPosition + 1));
            if ($comment === '') {
                throw new Exception('Missing comment');
            }
            $matches = null;
            if (strpos($comment, 'NA ') === 0) {
                $result->comment = ltrim(substr($comment, 3));
            } elseif (preg_match('/^(\d+(?:\.\d+)*) /', $comment, $matches)) {
                $result->fromVersion = $matches[1];
                $result->comment = ltrim(substr($comment, strlen($matches[0])));
            } else {
                throw new Exception('Malformed comment');
            }
            $fields = array();
            foreach (explode(';', $data) as $field) {
                $fields[] = trim($field);
            }
            $result->range = static::parseCodepointList($fields[0], '..', true, 2);
            switch (count($fields)) {
                case 4:
                    $result->statusIDNA2008 = $fields[3];
                    /* @noinspection PhpMissingBreakStatementInspection */
                case 3:
                    if ($fields[2] !== '') {
                        $result->mapping = static::parseCodepointList($fields[2], ' ');
                    }
                    /* @noinspection PhpMissingBreakStatementInspection */
                case 2:
                    $result->status = $fields[1];
                    break;
                case 1:
                    throw new Exception('Too few fields');
                default:
                    throw new Exception('Too many fields');
            }
        }

        return $result;
    }

    /**
     * Parse a range (eg '0123' or '2345...BCDEF').
     *
     * @param string $text
     * @param string $separator
     * @param bool $mustBeSorted
     * @param int|null $maxCount
     *
     * @throws \Exception
     *
     * @return int[]
     */
    protected static function parseCodepointList($text, $separator, $mustBeSorted = false, $maxCount = null)
    {
        $codePoints = array();
        $count = 0;
        foreach (explode($separator, (string) $text) as $part) {
            ++$count;
            if ($maxCount !== null && $count > $maxCount) {
                throw new Exception("Invalid range: $text");
            }
            if (!preg_match('/^[0-9a-fA-F]{4,6}$/', $part)) {
                throw new Exception("Invalid codepoint: $part");
            }
            $codePoint = hexdec($part);
            $codePoints[] = $codePoint;
            if ($mustBeSorted && $count > 1 && $codePoint <= $codePoints[$count - 2]) {
                throw new Exception("Invalid range: $text");
            }
        }

        return $codePoints;
    }
}
