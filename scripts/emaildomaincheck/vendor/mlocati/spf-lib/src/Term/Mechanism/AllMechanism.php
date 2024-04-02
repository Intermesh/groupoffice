<?php

declare(strict_types=1);

namespace SPFLib\Term\Mechanism;

use SPFLib\Term\Mechanism;

/**
 * Class that represents the "all" mechanism.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-5.1
 */
class AllMechanism extends Mechanism
{
    /**
     * The handle that identifies this mechanism.
     *
     * @var string
     */
    public const HANDLE = 'all';

    /**
     * Initialize the instance.
     *
     * @param string $qualifier the qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants)
     */
    public function __construct(string $qualifier)
    {
        parent::__construct($qualifier);
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term::__toString()
     */
    public function __toString(): string
    {
        return $this->getQualifier(true) . static::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term\Mechanism::getName()
     */
    public function getName(): string
    {
        return static::HANDLE;
    }
}
