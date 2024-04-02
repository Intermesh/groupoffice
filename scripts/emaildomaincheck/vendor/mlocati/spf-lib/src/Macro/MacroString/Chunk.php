<?php

declare(strict_types=1);

namespace SPFLib\Macro\MacroString;

/**
 * Class that represents a chunk of a MacroString.
 */
abstract class Chunk
{
    /**
     * Get the textual representation of this chunk (to be used in the DNS TXT record).
     */
    abstract public function __toString(): string;
}
