<?php

namespace MLocati\IDNA\IdnaMapping\Range;

use Exception;
use MLocati\IDNA\IdnaMapping\TableRow;

abstract class Range
{
    /**
     * One or two code points.
     *
     * @var int[]
     */
    public $range;

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
     * Initializes the instance.
     *
     * @param \MLocati\IDNA\IdnaMapping\TableRow $row The row read from the IDNA Mapping table
     */
    protected function __construct(TableRow $row)
    {
        $this->range = $row->range;
        $this->fromVersion = $row->fromVersion;
        $this->comment = $row->comment;
    }

    /**
     * Parse a line from the IDNA Mapping table.
     *
     * @param string $line
     *
     * @throws \Exception
     *
     * @return static|null
     */
    public static function fromIdnaMappingTableLine($line)
    {
        $result = null;
        $row = TableRow::parse($line);
        if ($row !== null) {
            switch ($row->status) {
                case 'deviation':
                    $result = new Deviation($row);
                    break;
                case 'disallowed':
                    $result = new Disallowed($row);
                    break;
                case 'ignored':
                    $result = new Ignored($row);
                    break;
                case 'mapped':
                    $result = new Mapped($row, false);
                    break;
                case 'disallowed_STD3_mapped':
                    $result = new Mapped($row, true);
                    break;
                case 'valid':
                    $result = new Valid($row, false);
                    break;
                case 'disallowed_STD3_valid':
                    $result = new Valid($row, true);
                    break;
                default:
                    throw new Exception('invalid status: '.$row->status);
            }
        }

        return $result;
    }

    /**
     * Splits this instance into multiple instances, so that each one is a single-codepoint.
     *
     * @return static[]
     */
    public function expandRange()
    {
        $result = array();
        if (isset($this->range[1])) {
            $rangeSize = $this->range[1] - $this->range[0] + 1;
            $comments = explode('..', $this->comment);
            if (count($comments) === $rangeSize) {
                for ($i = 0; $i < $rangeSize; ++$i) {
                    $comments[$i] = trim($comments[$i]);
                }
            } else {
                $comments = null;
            }
            for ($codepoint = $this->range[0]; $codepoint <= $this->range[1]; ++$codepoint) {
                $clone = clone $this;
                if ($comments !== null) {
                    $clone->comment = array_shift($comments);
                }
                $clone->range = array($codepoint);
                $result[] = $clone;
            }
        } else {
            $result[] = $this;
        }

        return $result;
    }

    /**
     * Check if another instance has data compatible with this instance.
     *
     * @param \MLocati\IDNA\IdnaMapping\Range\Range $range
     *
     * @return bool
     */
    abstract protected function isCompatibleWith(Range $range);

    /**
     * Merge this range with another one, if they are contiguous and are compatible.
     *
     * @param \MLocati\IDNA\IdnaMapping\Range\Range $range
     *
     * @return false
     */
    public function merge(Range $range)
    {
        $result = false;
        if (
            end($this->range) === $range->range[0] - 1
            &&
            $this->fromVersion === $range->fromVersion
            &&
            $this->isCompatibleWith($range)
        ) {
            $this->range[1] = end($range->range);
            if ($this->comment === '') {
                $this->comment = $range->comment;
            } elseif ($range->comment !== '' && $range->comment !== $this->comment) {
                $this->comment .= '..'.$range->comment;
            }
            $result = true;
        }

        return $result;
    }
}
