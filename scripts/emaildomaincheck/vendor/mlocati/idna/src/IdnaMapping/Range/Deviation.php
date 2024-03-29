<?php

namespace MLocati\IDNA\IdnaMapping\Range;

use Exception;
use MLocati\IDNA\IdnaMapping\TableRow;

/**
 * If the processing is transitional the code point is mapped, otherwise it is valid.
 */
class Deviation extends Range
{
    /**
     * The replacement.
     * It's null if we are unable to determine the IDNA2003 replacement, list of code points otherwise.
     *
     * @var int[]|null
     */
    public $idna2003Replacement;

    /**
     * Initializes the instance.
     *
     * @param \MLocati\IDNA\IdnaMapping\TableRow $row
     *
     * @throws \Exception
     */
    public function __construct(TableRow $row)
    {
        parent::__construct($row);
        $this->idna2003Replacement = $row->mapping;
        if ($row->statusIDNA2008 !== '') {
            throw new Exception('IDNA2008 status field unexpected in deviation ranges');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\IdnaMapping\Range\Range::isCompatibleWith()
     */
    protected function isCompatibleWith(Range $range)
    {
        return $range instanceof self && $range->idna2003Replacement === $this->idna2003Replacement;
    }
}
