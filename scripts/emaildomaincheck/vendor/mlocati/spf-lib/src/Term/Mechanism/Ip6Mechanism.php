<?php

declare(strict_types=1);

namespace SPFLib\Term\Mechanism;

use IPLib\Address\IPv6;
use SPFLib\Term\Mechanism;

/**
 * Class that represents the "ip6" mechanism.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-5.6
 */
class Ip6Mechanism extends Mechanism
{
    /**
     * The handle that identifies this mechanism.
     *
     * @var string
     */
    public const HANDLE = 'ip6';

    /**
     * @var \IPLib\Address\IPv6
     */
    private $ip;

    /**
     * @var int
     */
    private $cidrLength;

    /**
     * Initialize the instance.
     *
     * @param string $qualifier the qualifier of this mechanism (the value of one of the Mechanism::QUALIFIER_... constants)
     */
    public function __construct(string $qualifier, IPv6 $ip, ?int $cidrLength = null)
    {
        parent::__construct($qualifier);
        $this->ip = $ip;
        $this->cidrLength = $cidrLength === null ? 128 : $cidrLength;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term::__toString()
     */
    public function __toString(): string
    {
        $result = $this->getQualifier(true) . static::HANDLE . ':' . (string) $this->getIP();
        $cidrLength = $this->getCidrLength();
        if ($cidrLength !== 128) {
            $result .= '/' . (string) $cidrLength;
        }

        return $result;
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

    public function getIP(): IPv6
    {
        return $this->ip;
    }

    public function getCidrLength(): int
    {
        return $this->cidrLength;
    }
}
