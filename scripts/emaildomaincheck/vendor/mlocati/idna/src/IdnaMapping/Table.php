<?php

namespace MLocati\IDNA\IdnaMapping;

use Exception;
use MLocati\IDNA\Exception\InvalidParameter;
use MLocati\IDNA\IdnaMapping\Range\Deviation;
use MLocati\IDNA\IdnaMapping\Range\Disallowed;
use MLocati\IDNA\IdnaMapping\Range\Ignored;
use MLocati\IDNA\IdnaMapping\Range\Mapped;
use MLocati\IDNA\IdnaMapping\Range\Range;
use MLocati\IDNA\IdnaMapping\Range\Valid;

class Table
{
    /**
     * Version of the data (if found).
     *
     * @var string
     */
    public $version;

    /**
     * ISO 8601 date of the data (if found).
     *
     * @var string
     */
    public $date;

    /**
     * @var \MLocati\IDNA\IdnaMapping\Range\Range[]
     */
    public $ranges;

    /**
     * @param string $text
     *
     * @throws \MLocati\IDNA\Exception\Exception
     */
    protected function __construct($text)
    {
        $this->parseText($text);
    }

    /**
     * @param array $options {
     *     @var string $namespace The class namespace [default: 'MLocati\IDNA']
     *     @var string $className The class name [default: 'IdnaMap']
     *     @var bool $comments Include comments? [default: false]
     *     @var bool $disallowed Include disallowed? [default: false]
     * }
     */
    public function buildMapClass(array $options = array())
    {
        $options += array(
            'namespace' => 'MLocati\IDNA',
            'className' => 'IdnaMap',
            'comments' => false,
            'disallowed' => false,
        );
        $validNames = array();
        // Opening
        $rows = array('<?php');
        $rows[] = '';
        $rows[] = "namespace {$options['namespace']};";
        $rows[] = '';
        $rows[] = "class {$options['className']}";
        $rows[] = '{';
        $rows[] = '    /**';
        $rows[] = '     * Valid, never excluded.';
        $rows[] = '     *';
        $rows[] = '     * @var int';
        $rows[] = '     */';
        $rows[] = '    const EXCLUDE_NO = '.Valid::EXCLUDE_NO.';';
        $validNames[Valid::EXCLUDE_NO] = 'EXCLUDE_NO';
        $rows[] = '';
        $rows[] = '    /**';
        $rows[] = '     * Range is excluded by IDNA2008 from all domain names for all versions of Unicode.';
        $rows[] = '     *';
        $rows[] = '     * @var int';
        $rows[] = '     */';
        $rows[] = '    const EXCLUDE_ALWAYS = '.Valid::EXCLUDE_ALWAYS.';';
        $validNames[Valid::EXCLUDE_ALWAYS] = 'EXCLUDE_ALWAYS';
        $rows[] = '';
        $rows[] = '    /**';
        $rows[] = '     * Range is excluded by IDNA2008 for the current version of Unicode.';
        $rows[] = '     *';
        $rows[] = '     * @var int';
        $rows[] = '     */';
        $rows[] = '    const EXCLUDE_CURRENT = '.Valid::EXCLUDE_CURRENT.';';
        $validNames[Valid::EXCLUDE_CURRENT] = 'EXCLUDE_CURRENT';
        // Deviations
        $rows[] = '';
        $rows[] = '    protected static $deviations = array(';
        foreach ($this->ranges as $range) {
            if ($range instanceof Deviation) {
                foreach ($range->expandRange() as $deviation) {
                    $row = '        '.$deviation->range[0].' => ';
                    if ($deviation->idna2003Replacement === null) {
                        $row .= 'array()';
                    } else {
                        $row .= 'array('.implode(', ', $deviation->idna2003Replacement).')';
                    }
                    $row .= ',';
                    if ($options['comments'] && $deviation->comment !== '') {
                        $row .= ' //'.$deviation->comment;
                    }
                    $rows[] = $row;
                }
            }
        }
        $rows[] = '    );';
        $rows[] = '';
        $rows[] = '    /**';
        $rows[] = '     * Get the IDNA2003 deviation from IDNA2008 for a specific code point.';
        $rows[] = '     *';
        $rows[] = '     * @param int $codepoint';
        $rows[] = '     *';
        $rows[] = '     * @return int[]|null';
        $rows[] = '     */';
        $rows[] = '    public static function getDeviation($codepoint)';
        $rows[] = '    {';
        $rows[] = '        return isset(static::$deviations[$codepoint]) ? static::$deviations[$codepoint] : null;';
        $rows[] = '    }';
        // Disallowed
        if ($options['disallowed']) {
            $disallowedSingle = array();
            $disallowedRange = array();
            foreach ($this->ranges as $range) {
                if ($range instanceof Disallowed) {
                    if (!isset($range->range[1])) {
                        $disallowedSingle[] = $range;
                    } elseif ($range->range[1] - $range->range[0] < 20) {
                        foreach ($range->expandRange() as $splitted) {
                            $disallowedSingle[] = $splitted;
                        }
                    } else {
                        $disallowedRange[] = $range;
                    }
                }
            }
            $rows[] = '';
            $rows[] = '    protected static $disallowedSingle = array(';
            foreach ($disallowedSingle as $disallowed) {
                $row = '        '.$disallowed->range[0].',';
                if ($options['comments'] && $disallowed->comment !== '') {
                    $row .= ' //'.$disallowed->comment;
                }
                $rows[] = $row;
            }
            $rows[] = '    );';
            $rows[] = '';
            $rows[] = '    /**';
            $rows[] = '     * Check if a code point is disallowed.';
            $rows[] = '     *';
            $rows[] = '     * @param int $codepoint';
            $rows[] = '     * @param bool $useSTD3ASCIIRules';
            $rows[] = '     *';
            $rows[] = '     * @return bool';
            $rows[] = '     */';
            $rows[] = '    public static function isDisallowed($codepoint, $useSTD3ASCIIRules = true)';
            $rows[] = '    {';
            $rows[] = '        $result = false;';
            $rows[] = '        if (';
            $rows[] = '            in_array($codepoint, static::$disallowedSingle, true)';
            foreach ($disallowedRange as $disallowed) {
                $row = '            || ($codepoint >= '.$disallowed->range[0].' && $codepoint <= '.$disallowed->range[1].')';
                if ($options['comments'] && $disallowed->comment !== '') {
                    $row .= ' //'.$disallowed->comment;
                }
                $rows[] = $row;
            }
            $rows[] = '        ) {';
            $rows[] = '            $result = true;';
            $rows[] = '        } elseif ($useSTD3ASCIIRules) {';
            $rows[] = '            if (';
            $rows[] = '                isset(static::$mappedDisallowedSTD3[$codepoint])';
            $rows[] = '                || isset(static::$validDisallowedSTD3[$codepoint])';
            $rows[] = '            ) {';
            $rows[] = '                $result = true;';
            $rows[] = '            }';
            $rows[] = '        }';
            $rows[] = '';
            $rows[] = '        return $result;';
            $rows[] = '    }';
        }
        // Ignored
        $ignoredSingle = array();
        $ignoredRange = array();
        foreach ($this->ranges as $range) {
            if ($range instanceof Ignored) {
                if (!isset($range->range[1])) {
                    $ignoredSingle[] = $range;
                } elseif ($range->range[1] - $range->range[0] < 20) {
                    foreach ($range->expandRange() as $splitted) {
                        $ignoredSingle[] = $splitted;
                    }
                } else {
                    $ignoredRange[] = $range;
                }
            }
        }
        $rows[] = '';
        $rows[] = '    protected static $ignoredSingle = array(';
        foreach ($ignoredSingle as $ignored) {
            $row = '        '.$ignored->range[0].',';
            if ($options['comments'] && $ignored->comment !== '') {
                $row .= ' //'.$ignored->comment;
            }
            $rows[] = $row;
        }
        $rows[] = '    );';
        $rows[] = '';
        $rows[] = '    /**';
        $rows[] = '     *Check if a codepoint is ignored.';
        $rows[] = '     *';
        $rows[] = '     *@param int $codepoint';
        $rows[] = '     *';
        $rows[] = '     *@return bool';
        $rows[] = '     */';
        $rows[] = '    public static function isIgnored($codepoint)';
        $rows[] = '    {';
        $rows[] = '        $result = false;';
        $rows[] = '        if (';
        $rows[] = '            in_array($codepoint, static::$ignoredSingle, true)';
        foreach ($ignoredRange as $ignored) {
            $row = '            || ($codepoint >= '.$ignored->range[0].' && $codepoint <= '.$ignored->range[1].')';
            if ($options['comments'] && $ignored->comment !== '') {
                $row .= ' //'.$ignored->comment;
            }
            $rows[] = $row;
        }
        $rows[] = '        ) {';
        $rows[] = '            $result = true;';
        $rows[] = '        }';
        $rows[] = '';
        $rows[] = '        return $result;';
        $rows[] = '    }';
        // Mapped
        $mapped = array(
            0 => array(),
            1 => array(),
        );
        foreach ($this->ranges as $range) {
            if ($range instanceof Mapped) {
                $index = $range->disallowedSTD3 ? 1 : 0;
                foreach ($range->expandRange() as $m) {
                    $mapped[$index][] = $m;
                }
            }
        }
        foreach (array(0 => '$mapped', 1 => '$mappedDisallowedSTD3') as $index => $varName) {
            $rows[] = '';
            $rows[] = '    protected static '.$varName.' = array(';
            foreach ($mapped[$index] as $m) {
                $row = '        '.$m->range[0].' => array('.implode(', ', $m->to).')';
                $row .= ',';
                if ($options['comments'] && $m->comment !== '') {
                    $row .= ' // '.$m->comment;
                }
                $rows[] = $row;
            }
            $rows[] = '    );';
        }
        $rows[] = '';
        $rows[] = '    /**';
        $rows[] = '     * Get the mapping for a specific code point.';
        $rows[] = '     *';
        $rows[] = '     * @param int $codepoint';
        $rows[] = '     * @param bool $useSTD3ASCIIRules';
        $rows[] = '     *';
        $rows[] = '     * @return int[]|null';
        $rows[] = '     */';
        $rows[] = '    public static function getMapped($codepoint, $useSTD3ASCIIRules = true)';
        $rows[] = '    {';
        $rows[] = '        $result = null;';
        $rows[] = '        if (isset(static::$mapped[$codepoint])) {';
        $rows[] = '            $result = static::$mapped[$codepoint];';
        $rows[] = '        } elseif (!$useSTD3ASCIIRules && isset(static::$mappedDisallowedSTD3[$codepoint])) {';
        $rows[] = '            $result = static::$mappedDisallowedSTD3[$codepoint];';
        $rows[] = '        }';
        $rows[] = '';
        $rows[] = '        return $result;';
        $rows[] = '    }';
        // Valid
        $validSingle = array(
            0 => array(),
            1 => array(),
        );
        $validRange = array();
        foreach ($this->ranges as $range) {
            if ($range instanceof Valid) {
                $singleIndex = $range->disallowedSTD3 ? 1 : 0;
                if (!isset($range->range[1])) {
                    $validSingle[$singleIndex][] = $range;
                } elseif ($range->disallowedSTD3 || $range->range[1] - $range->range[0] < 27) {
                    foreach ($range->expandRange() as $splitted) {
                        $validSingle[$singleIndex][] = $splitted;
                    }
                } else {
                    $validRange[] = $range;
                }
            }
        }
        foreach (array(0 => '$validSingle', 1 => '$validDisallowedSTD3') as $index => $varName) {
            $rows[] = '';
            $rows[] = '    protected static '.$varName.' = array(';
            foreach ($validSingle[$index] as $v) {
                /* @var Valid $v */
                $row = '        '.$v->range[0].' => ';
                if ($options['comments']) {
                    $row .= '/*'.$validNames[$v->exclude].'*/';
                }
                $row .= $v->exclude.',';
                if ($options['comments'] && $v->comment !== '') {
                    $row .= ' // '.$v->comment;
                }
                $rows[] = $row;
            }
            $rows[] = '    );';
        }
        $rows[] = '';
        $rows[] = '    /**';
        $rows[] = '     * Check if a code point is valid.';
        $rows[] = '     *';
        $rows[] = '     * @param int $codepoint';
        $rows[] = '     * @param int[] $exclude';
        $rows[] = '     * @param bool $useSTD3ASCIIRules';
        $rows[] = '     *';
        $rows[] = '     * @return bool';
        $rows[] = '     */';
        $rows[] = '    public static function isValid($codepoint, array $exclude = array(), $useSTD3ASCIIRules = true)';
        $rows[] = '    {';
        $rows[] = '        $excluded = null;';
        $rows[] = '        if (isset(static::$validSingle[$codepoint])) {';
        $rows[] = '            $excluded = static::$validSingle[$codepoint];';
        $rows[] = '        } elseif (!$useSTD3ASCIIRules && isset(static::$validDisallowedSTD3[$codepoint])) {';
        $rows[] = '            $excluded = static::$validDisallowedSTD3[$codepoint];';
        $first = true;
        foreach ($validRange as $range) {
            $row = '            ';
            if ($first) {
                $rows[] = '        } elseif (';
                $first = false;
            } else {
                $row .= '|| ';
            }
            $row .= '($codepoint >= '.$range->range[0].' && $codepoint <= '.$range->range[1].' && $excluded = static::'.$validNames[$range->exclude].')';
            if ($options['comments'] && $range->comment !== '') {
                $row .= ' //'.$range->comment;
            }
            $rows[] = $row;
        }
        if (!$first) {
            $rows[] = '        ) {';
            $rows[] = '            // noop';
        }
        $rows[] = '        }';
        $rows[] = '        $result = false;';
        $rows[] = '        if ($excluded !== null && !in_array($excluded, $exclude)) {';
        $rows[] = '            $result = true;';
        $rows[] = '        }';
        $rows[] = '';
        $rows[] = '        return $result;';
        $rows[] = '    }';
        // Closing
        $rows[] = '}';
        $rows[] = '';

        return implode("\n", $rows);
    }

    /**
     * @param string $path
     *
     * @throws \MLocati\IDNA\Exception\Exception
     *
     * @return static
     */
    public static function load($path)
    {
        $path = (string) $path;
        if ($path === '') {
            throw new InvalidParameter(__METHOD__, '$path', 'Path is not specified');
        }
        if (!preg_match('/^\w+:\/\//', $path)) {
            if (!is_file($path)) {
                throw new InvalidParameter(__METHOD__, '$path', "File not found: $path");
            }
            if (!is_readable($path)) {
                throw new InvalidParameter(__METHOD__, '$path', "File not readable: $path");
            }
        }
        $text = @file_get_contents($path);
        if ($text === false) {
            throw new InvalidParameter(__METHOD__, '$path', "Failed to read from $path");
        }

        return new static($text);
    }

    /**
     * @param string $text
     *
     * @throws \MLocati\IDNA\Exception\Exception
     *
     * @return static
     */
    public static function parse($text)
    {
        return new static($text);
    }

    /**
     * @param string $text
     *
     * @throws \MLocati\IDNA\Exception\Exception
     */
    protected function parseText($text)
    {
        $version = '';
        $date = '';
        $ranges = array();
        $text = (string) $text;
        if ($text === '') {
            throw new InvalidParameter(__METHOD__, '$text', 'Empty text');
        }
        $lines = preg_split('/\r\n|\n|\r/m', $text);
        $matches = null;
        foreach ($lines as $lineIndex => $line) {
            try {
                $range = Range::fromIdnaMappingTableLine($line);
            } catch (Exception $x) {
                throw new \Exception('Invalid line #'.($lineIndex + 1).': '.$x->getMessage());
            }
            if ($range === null) {
                if (empty($ranges)) {
                    if ($version === '' && preg_match('/^#\s*IdnaMappingTable-(\d+(?:\.\d+)*)\.txt/i', $line, $matches)) {
                        $version = $matches[1];
                    } elseif ($date === '' && preg_match('/^#\s*Date\s*:?\s*(.+?)\s*$/i', $line, $matches)) {
                        $timestamp = @strtotime($matches[1]);
                        if ($timestamp !== false) {
                            $date = date('c', $timestamp);
                        }
                    }
                }
            } else {
                $ranges[] = $range;
            }
        }
        usort(
            $ranges,
            function (Range $a, Range $b) {
                return $a->range[0] - $b->range[0];
            }
        );
        $merged = array();
        $count = count($ranges);
        $previous = null;
        for ($index = 0; $index < $count; ++$index) {
            $range = $ranges[$index];
            /* @var Range $range */
            /* @var Range $previous */
            if ($previous === null) {
                $added = false;
            } else {
                $added = $previous->merge($range);
            }
            if (!$added) {
                $previous = $range;
                $merged[] = $range;
            }
        }
        $this->version = $version;
        $this->date = $date;
        $this->ranges = $merged;
    }
}
