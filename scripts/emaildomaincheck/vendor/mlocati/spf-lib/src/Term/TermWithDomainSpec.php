<?php

declare(strict_types=1);

namespace SPFLib\Term;

use SPFLib\Macro\MacroString;

/**
 * Class that all terms with a domain-spec property must implement.
 */
interface TermWithDomainSpec
{
    /**
     * Get the domain-spec instance.
     */
    public function getDomainSpec(): MacroString;
}
