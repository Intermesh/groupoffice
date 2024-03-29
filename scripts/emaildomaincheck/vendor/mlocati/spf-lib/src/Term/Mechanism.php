<?php

declare(strict_types=1);

namespace SPFLib\Term;

use SPFLib\Term;

/**
 * Class that any SPF record mechanism must implement.
 */
abstract class Mechanism implements Term
{
    /**
     * Modifier qualifier: pass.
     *
     * @var string
     */
    public const QUALIFIER_PASS = '+';

    /**
     * Modifier qualifier: fail.
     *
     * @var string
     */
    public const QUALIFIER_FAIL = '-';

    /**
     * Modifier qualifier: softfail.
     *
     * @var string
     */
    public const QUALIFIER_SOFTFAIL = '~';

    /**
     * Modifier qualifier: neutral.
     *
     * @var string
     */
    public const QUALIFIER_NEUTRAL = '?';

    /**
     * The qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants).
     *
     * @var string
     */
    private $qualifier;

    /**
     * Initialize the instance.
     *
     * @param string $qualifier the qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants)
     */
    protected function __construct(string $qualifier)
    {
        $this->qualifier = $qualifier;
    }

    /**
     * Get the name of the modifier (the part before ':' or '/').
     */
    abstract public function getName(): string;

    /**
     * Get the qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants).
     *
     * @param bool $emptyIfInclude use true to return an empty string if the qualifier is QUALIFIER_PASS
     */
    public function getQualifier(bool $emptyIfPass = false): string
    {
        return $emptyIfPass && $this->qualifier === static::QUALIFIER_PASS ? '' : $this->qualifier;
    }
}
