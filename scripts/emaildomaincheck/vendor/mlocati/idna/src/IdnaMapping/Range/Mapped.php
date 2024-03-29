<?php

namespace MLocati\IDNA\IdnaMapping\Range;

use Exception;
use MLocati\IDNA\IdnaMapping\TableRow;

/**
 * The code point is replaced in the string by the value for the mapping.
 */
class Mapped extends Range
{
    /**
     * The replacement code points.
     *
     * @var int[]
     */
    public $to;

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
        if ($row->mapping === null) {
            throw new Exception('Missing Mapping field in mapped ranges');
        }
        $this->to = $row->mapping;
        if ($row->statusIDNA2008 !== '') {
            throw new Exception('IDNA2008 Status field unexpected in mapped ranges');
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
        return $range instanceof self && $range->to === $this->to && $range->disallowedSTD3 === $range->disallowedSTD3;
    }
}
