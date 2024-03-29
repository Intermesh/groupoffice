<?php

declare(strict_types=1);

namespace SPFLib\Macro\MacroString\Chunk;

use SPFLib\Macro\MacroString\Chunk;

/**
 * Class that represents a literal part of a MacroString.
 */
class LiteralString extends Chunk
{
    /**
     * The string to be used in the DNS record.
     *
     * @var string
     */
    private $dnsString;

    /**
     * Initialize the instance.
     *
     * @param string $dnsString The string to be used in the DNS record
     */
    public function __construct(string $dnsString)
    {
        $this->dnsString = $dnsString;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Macro\MacroString\Chunk::__toString()
     */
    public function __toString(): string
    {
        return $this->dnsString;
    }
}
