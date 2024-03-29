<?php

namespace MLocati\IDNA\IdnaMapping\Range;

use Exception;
use MLocati\IDNA\IdnaMapping\TableRow;

/**
 * The code point is valid, and not modified.
 */
class Valid extends Range
{
    /**
     * This range is never excluded.
     *
     * @var int
     */
    const EXCLUDE_NO = 1;

    /**
     * Range is excluded by IDNA2008 from all domain names for all versions of Unicode.
     *
     * @var int
     */
    const EXCLUDE_ALWAYS = 2;

    /**
     * Range is excluded by IDNA2008 for the current version of Unicode.
     *
     * @var int
     */
    const EXCLUDE_CURRENT = 3;

    /**
     * Range is excluded? (one of the EXCLUDE_... constants).
     *
     * @var int
     */
    public $exclude;

    /**
     * The status is disallowed if UseSTD3ASCIIRules=true (the normal case); implementations that allow UseSTD3ASCIIRules=false would treat the code point as mapped.
     *
     * @var bool
     */
    public $disallowedSTD3;

    /**
     * Initializes the instance.
     *
     * @param \MLocati\IDNA\IdnaMapping\TableRow $row
     * @param bool $disallowedSTD3
     *
     * @throws \Exception
     */
    public function __construct(TableRow $row, $disallowedSTD3)
    {
        parent::__construct($row);
        if ($row->mapping !== null) {
            throw new Exception('Mapping field unexpected in valid ranges');
        }
        if ($row->statusIDNA2008 === '') {
            $this->exclude = static::EXCLUDE_NO;
        } else {
            switch ($row->statusIDNA2008) {
                case 'NV8':
                    $this->exclude = static::EXCLUDE_ALWAYS;
                    break;
                case 'XV8':
                    $this->exclude = static::EXCLUDE_CURRENT;
                    break;
                default:
                    throw new Exception('IDNA2008 status field with an invalid value for valid ranges');
            }
        }
        $this->disallowedSTD3 = $disallowedSTD3;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\IdnaMapping\Range\Range::isCompatibleWith()
     */
    protected function isCompatibleWith(Range $range)
    {
        return $range instanceof self && $range->exclude === $this->exclude && $range->disallowedSTD3 === $this->disallowedSTD3;
    }
}
