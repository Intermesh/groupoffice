<?php

declare(strict_types=1);

namespace SPFLib\Term\Mechanism;

use SPFLib\Macro\MacroString;
use SPFLib\Term\Mechanism;
use SPFLib\Term\TermWithDomainSpec;

/**
 * Class that represents the "exists" mechanism.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-5.7
 */
class ExistsMechanism extends Mechanism implements TermWithDomainSpec
{
    /**
     * The handle that identifies this mechanism.
     *
     * @var string
     */
    public const HANDLE = 'exists';

    /**
     * @var \SPFLib\Macro\MacroString
     */
    private $domainSpec;

    /**
     * Initialize the instance.
     *
     * @param string $qualifier the qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants)
     * @param \SPFLib\Macro\MacroString|string $domainSpec
     *
     * @throws \SPFLib\Exception\InvalidMacroStringException if $domainSpec is a string which does not represent a valid MacroString, or if it's an empty MacroString instance
     */
    public function __construct(string $qualifier, $domainSpec)
    {
        parent::__construct($qualifier);
        if (!$domainSpec instanceof MacroString || $domainSpec->isEmpty()) {
            $domainSpec = MacroString\Decoder::getInstance()->decode((string) $domainSpec);
        }

        $this->domainSpec = $domainSpec;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term::__toString()
     */
    public function __toString(): string
    {
        return $this->getQualifier(true) . static::HANDLE . ':' . (string) $this->getDomainSpec();
    }

    public function __clone()
    {
        $this->domainSpec = clone $this->getDomainSpec();
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

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term\TermWithDomainSpec::getDomainSpec()
     */
    public function getDomainSpec(): MacroString
    {
        return $this->domainSpec;
    }
}
