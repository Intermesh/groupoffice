<?php

declare(strict_types=1);

namespace SPFLib\Term\Mechanism;

use SPFLib\Macro\MacroString;
use SPFLib\Term\Mechanism;
use SPFLib\Term\TermWithDomainSpec;

/**
 * Class that represents the "ptr" mechanism.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-5.5
 */
class PtrMechanism extends Mechanism implements TermWithDomainSpec
{
    /**
     * The handle that identifies this mechanism.
     *
     * @var string
     */
    public const HANDLE = 'ptr';

    /**
     * @var \SPFLib\Macro\MacroString
     */
    private $domainSpec;

    /**
     * Initialize the instance.
     *
     * @param string $qualifier the qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants)
     * @param \SPFLib\Macro\MacroString|string|null $domainSpec
     */
    public function __construct(string $qualifier, $domainSpec = null)
    {
        parent::__construct($qualifier);
        if (!$domainSpec instanceof MacroString) {
            $domainSpec = MacroString\Decoder::getInstance()->decode($domainSpec === null ? '' : $domainSpec, MacroString\Decoder::FLAG_ALLOWEMPTY);
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
        $result = $this->getQualifier(true) . static::HANDLE;
        $domainSpec = $this->getDomainSpec();
        if (!$domainSpec->isEmpty()) {
            $result .= ':' . (string) $domainSpec;
        }

        return $result;
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
