<?php

declare(strict_types=1);

namespace SPFLib\Term\Mechanism;

use SPFLib\Macro\MacroString;
use SPFLib\Term\Mechanism;
use SPFLib\Term\TermWithDomainSpec;

/**
 * Class that represents the "a" mechanism.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-5.3
 */
class AMechanism extends Mechanism implements TermWithDomainSpec
{
    /**
     * The handle that identifies this mechanism.
     *
     * @var string
     */
    public const HANDLE = 'a';

    /**
     * @var \SPFLib\Macro\MacroString
     */
    private $domainSpec;

    /**
     * @var int
     */
    private $ip4CidrLength;

    /**
     * @var int
     */
    private $ip6CidrLength;

    /**
     * Initialize the instance.
     *
     * @param string $qualifier the qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants)
     * @param \SPFLib\Macro\MacroString|string|null $domainSpec
     *
     * @throws \SPFLib\Exception\InvalidMacroStringException if $domainSpec is a non empty string which does not represent a valid MacroString
     */
    public function __construct(string $qualifier, $domainSpec = null, ?int $ip4CidrLength = null, ?int $ip6CidrLength = null)
    {
        parent::__construct($qualifier);
        if (!$domainSpec instanceof MacroString) {
            $domainSpec = MacroString\Decoder::getInstance()->decode($domainSpec === null ? '' : $domainSpec, MacroString\Decoder::FLAG_ALLOWEMPTY);
        }
        $this->domainSpec = $domainSpec;
        $this->ip4CidrLength = $ip4CidrLength === null ? 32 : $ip4CidrLength;
        $this->ip6CidrLength = $ip6CidrLength === null ? 128 : $ip6CidrLength;
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
        $ip4CidrLength = $this->getIp4CidrLength();
        if ($ip4CidrLength !== 32) {
            $result .= "/{$ip4CidrLength}";
        }
        $ip6CidrLength = $this->getIp6CidrLength();
        if ($ip6CidrLength !== 128) {
            $result .= "//{$ip6CidrLength}";
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

    public function getIp4CidrLength(): int
    {
        return $this->ip4CidrLength;
    }

    public function getIp6CidrLength(): int
    {
        return $this->ip6CidrLength;
    }
}
