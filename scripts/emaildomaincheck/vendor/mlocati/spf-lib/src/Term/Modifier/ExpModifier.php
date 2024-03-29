<?php

declare(strict_types=1);

namespace SPFLib\Term\Modifier;

use SPFLib\Macro\MacroString;
use SPFLib\Term\Modifier;
use SPFLib\Term\TermWithDomainSpec;

/**
 * Class that represents the "exp" modifier.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-6.2
 */
class ExpModifier extends Modifier implements TermWithDomainSpec
{
    /**
     * The handle that identifies this modifier.
     *
     * @var string
     */
    public const HANDLE = 'exp';

    /**
     * @var \SPFLib\Macro\MacroString
     */
    private $domainSpec;

    /**
     * Initialize the instance.
     *
     * @param \SPFLib\Macro\MacroString|string $domainSpec
     *
     * @throws \SPFLib\Exception\InvalidMacroStringException if $domainSpec is a string which does not represent a valid MacroString, or if it's an empty MacroString instance
     */
    public function __construct($domainSpec)
    {
        if (!$domainSpec instanceof MacroString || $domainSpec->isEmpty()) {
            $domainSpec = MacroString\Decoder::getInstance()->decode($domainSpec);
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
        return static::HANDLE . '=' . (string) $this->getDomainSpec();
    }

    public function __clone()
    {
        $this->domainSpec = clone $this->getDomainSpec();
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term\Modifier::getName()
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
