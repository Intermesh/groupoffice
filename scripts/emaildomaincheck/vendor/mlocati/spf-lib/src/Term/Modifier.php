<?php

declare(strict_types=1);

namespace SPFLib\Term;

use SPFLib\Term;

/**
 * Class that any SPF record modofier must implement.
 */
abstract class Modifier implements Term
{
    /**
     * Get the name of the modifier (the part before '=').
     */
    abstract public function getName(): string;
}
