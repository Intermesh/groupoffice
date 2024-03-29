<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;
use SPFLib\Macro\MacroString;

/**
 * Exception thrown when encountering an invalid domain.
 */
class InvalidDomainException extends Exception
{
    /**
     * The invalid domain name.
     *
     * @var string
     */
    private $domain;

    /**
     * The reason why the domain is not valid.
     *
     * @var string
     */
    private $reason;

    /**
     * The domain-spec instance from which the invalid domain has been derived.
     *
     * @var \SPFLib\Macro\MacroString|null
     */
    private $derivedFrom;

    /**
     * Initialize the instance.
     *
     * @param string $domain the invalid domain
     * @param string $reason the reason why the domain is not valid
     * @param \SPFLib\Macro\MacroString|null $defivedFrom the domain-spec instance from which the invalid domain has been derived
     */
    public function __construct(string $domain, string $reason, ?MacroString $defivedFrom = null)
    {
        parent::__construct($reason === '' ? "The domain '{$domain}' is not valid" : "The domain '{$domain}' is not valid: {$reason}");
        $this->reason = $reason;
        $this->domain = $domain;
        $this->derivedFrom = $defivedFrom;
    }

    /**
     * Get the invalid domain.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get the reason why the domain is not valid.
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Get the domain-spec instance from which the invalid domain has been derived.
     */
    public function getDerivedFrom(): ?MacroString
    {
        return $this->derivedFrom;
    }
}
