<?php

declare(strict_types=1);

namespace SPFLib;

/**
 * Interface that any SPF record term must implement.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-4.6
 */
interface Term
{
    /**
     * Get the string representation of the term.
     */
    public function __toString(): string;
}
