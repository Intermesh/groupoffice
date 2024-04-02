<?php

declare(strict_types=1);

namespace SPFLib\Check;

use IPLib\Address;
use IPLib\Address\AddressInterface;
use IPLib\Range\Subnet;
use SPFLib\DNS\Resolver;
use SPFLib\Exception;
use SPFLib\Macro\MacroString\Chunk\Placeholder;

/**
 * Class that holds the state of the check process.
 */
abstract class State
{
    /**
     * The maximum number of allowed DNS lookups.
     *
     * @var int
     *
     * @see https://tools.ietf.org/html/rfc7208#section-4.6.4
     */
    public const MAX_DNS_LOOKUPS = 10;

    /**
     * The maximum number of DNS IP lookups that returned zero addresses.
     *
     * @var int
     *
     * @see https://tools.ietf.org/html/rfc7208#section-11.1
     */
    public const MAX_VOID_DNS_LOOKUPS = 2;

    /**
     * The environment being checked.
     *
     * @var \SPFLib\Check\Environment
     */
    private $environment;

    /**
     * The DNS resolver instance to be used for queries.
     *
     * @var \SPFLib\DNS\Resolver
     */
    private $resolver;

    /**
     * The domain name derived from the reverse lookup of the SMTP client IP.
     *
     * @var string
     */
    private $clientIPDomain = '';

    /**
     * Cache the DNS reverse lookups already performed.
     *
     * @var array array keys are the string representation of an IP address, array values are the resolved addresses (empty string in case the reverse lookup failed)
     */
    private $reverseLookups = [];

    /**
     * Cache the PTR records for the client IP address.
     *
     * @var array|null
     */
    private $ptrPointers;

    /**
     * The number of DNS queries already performed.
     *
     * @var int
     */
    private $dnsLookupsCount;

    /**
     * The number of DNS IP lookups already performed that returned zero addresses.
     *
     * @var int
     */
    private $voidIPLookupsCount;

    /**
     * Initialize the instance.
     *
     * @param \SPFLib\Check\Environment $environment $the environment
     * @param \SPFLib\DNS\Resolver $resolver the DNS resolver instance to be used for queries
     */
    public function __construct(Environment $environment, Resolver $resolver)
    {
        $this->environment = $environment;
        $this->resolver = $resolver;
        $this->resetDNSQueryCounters();
    }

    /**
     * Get the environment being checked.
     *
     * @return \SPFLib\Check\Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * Get the sender email address currently being checked.
     *
     * @return string
     */
    abstract public function getSender(): string;

    /**
     * Get the local part of the sender email address currently being checked (that is, the part before '@').
     */
    public function getSenderLocalPart(): string
    {
        $sender = $this->getSender();
        $p = strpos($sender, '@');
        if ($p === false) {
            return '';
        }

        return substr($sender, 0, $p);
    }

    /**
     * Get the domain of the sender email address currently being checked (that is, the part after '@').
     */
    public function getSenderDomain(): string
    {
        $sender = $this->getSender();
        $p = strpos($sender, '@');
        if ($p === false) {
            return '';
        }

        return substr($sender, $p + 1);
    }

    /**
     * Get the domain name derived from the reverse lookup of the SMTP client IP.
     *
     * @throws \SPFLib\Exception\TooManyDNSLookupsException if too many DNS queries have been performed
     */
    public function getClientIPDomain(): string
    {
        $ip = $this->getEnvironment()->getClientIP();
        if ($ip === null) {
            return '';
        }
        $key = (string) $ip;
        if (!isset($this->reverseLookups[$key])) {
            $this->countDNSLookup();
            $domainName = $this->getDNSResolver()->getDomainNameFromIPAddress($ip);
            $this->reverseLookups[$key] = $domainName;
        }

        return $this->reverseLookups[$key];
    }

    /**
     * Reset the number of DNS queries already performed.
     *
     * @return self
     */
    public function resetDNSQueryCounters(): self
    {
        $this->dnsLookupsCount = 0;
        $this->voidIPLookupsCount = 0;

        return $this;
    }

    /**
     * Count a DNS lookup and, if we are over the limit, throw a TooManyDNSLookupsException exception.
     *
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     */
    public function countDNSLookup(int $number = 1): void
    {
        $this->dnsLookupsCount += $number;
        if ($this->dnsLookupsCount > static::MAX_DNS_LOOKUPS) {
            throw new Exception\TooManyDNSLookupsException(static::MAX_DNS_LOOKUPS);
        }
    }

    /**
     * Count a DNS IP lookup that returned zero addresses.
     *
     * @throws \SPFLib\Exception\TooManyDNSVoidLookupsException
     */
    public function countVoidIPLookupsCount(int $number = 1): void
    {
        $this->voidIPLookupsCount += $number;
        if ($this->voidIPLookupsCount > static::MAX_VOID_DNS_LOOKUPS) {
            throw new Exception\TooManyDNSVoidLookupsException(static::MAX_VOID_DNS_LOOKUPS);
        }
    }

    public function getValidatedDomain(string $targetDomain, bool $allowSubdomain): string
    {
        $pointers = $this->getPTRPointers();
        $targetDomainChunks = explode('.', trim($targetDomain, '.'));
        while ($allowSubdomain === false || isset($targetDomainChunks[1])) {
            $search = '.' . implode('.', $targetDomainChunks);
            foreach ($pointers as $pointer) {
                $pointerAddresses = $this->getDNSResolver()->getIPAddressesFromDomainName($pointer);
                foreach ($pointerAddresses as $pointerAddress) {
                    if ($this->matchIP($pointerAddress, 32, 128)) {
                        $compare = '.' . ltrim($pointer, '.');
                        if (strcasecmp($search, substr($compare, -strlen($search))) === 0) {
                            return $pointer;
                        }
                    }
                }
            }
            if ($allowSubdomain === false) {
                break;
            }
            array_shift($targetDomainChunks);
        }

        return '';
    }

    /**
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\TooManyDNSVoidLookupsException
     */
    public function matchDomainIPs(string $domain, ?int $ipv4CidrLength, ?int $ipv6CidrLength): bool
    {
        $ips = $this->getDNSResolver()->getIPAddressesFromDomainName($domain);
        if ($ips === []) {
            $this->countVoidIPLookupsCount();
        } else {
            foreach ($ips as $ip) {
                if ($this->matchIP($ip, $ipv4CidrLength, $ipv6CidrLength)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function matchIP(AddressInterface $check, ?int $ipv4CidrLength, ?int $ipv6CidrLength): bool
    {
        $clientIP = $this->getEnvironment()->getClientIP();
        if ($ipv4CidrLength === 0) {
            if ($clientIP instanceof Address\IPv4 && $check instanceof Address\IPv4) {
                return true;
            }
        } elseif ($ipv4CidrLength !== null) {
            $clientIPv4 = $clientIP instanceof Address\IPv6 ? $clientIP->toIPv4() : $clientIP;
            if ($clientIPv4 instanceof Address\IPv4) {
                $checkIPv4 = $check instanceof Address\IPv6 ? $check->toIPv4() : $check;
                if ($checkIPv4 instanceof Address\IPv4) {
                    $range = Subnet::fromString("{$checkIPv4}/{$ipv4CidrLength}");
                    if ($range !== null && $range->contains($clientIPv4)) {
                        return true;
                    }
                }
            }
        }
        if ($ipv6CidrLength === 0) {
            if ($clientIP instanceof Address\IPv6 && $check instanceof Address\IPv6) {
                return strpos((string) $clientIP, '.') === false;
            }
        } elseif ($ipv6CidrLength !== null) {
            $clientIPv6 = $clientIP instanceof Address\IPv4 ? $clientIP->toIPv6() : $clientIP;
            if ($clientIPv6 instanceof Address\IPv6) {
                $checkIPv4 = $check instanceof Address\IPv4 ? $check->toIPv6() : $check;
                if ($clientIPv6 instanceof Address\IPv6) {
                    $range = Subnet::fromString("{$checkIPv4}/{$ipv6CidrLength}");
                    if ($range !== null && $range->contains($clientIPv6)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @deprecated since version 3.1.2
     * @see \SPFLib\Check\State::getEnvironment()
     */
    public function getEnvoronment(): Environment
    {
        return $this->getEnvironment();
    }

    /**
     * Get the DNS resolver instance to be used for queries.
     */
    protected function getDNSResolver(): Resolver
    {
        return $this->resolver;
    }

    protected function getPTRPointers(): array
    {
        if ($this->ptrPointers === null) {
            $this->countDNSLookup();
            $pointers = $this->getDNSResolver()->getPTRRecords($this->buildPTRQuery());
            array_splice($pointers, static::MAX_DNS_LOOKUPS);
            $this->ptrPointers = $pointers;
        }

        return $this->ptrPointers;
    }

    protected function buildPTRQuery(): string
    {
        $ip = $this->getEnvironment()->getClientIP();
        if ($ip instanceof Address\IPv4) {
            return implode(
                '.',
                array_reverse($ip->getBytes())
            ) . '.in-addr.arpa';
        }
        if ($ip instanceof Address\IPv6) {
            return implode(
                '.',
                array_reverse(str_split(str_replace(':', '', $ip->toString(true)), 1))
            ) . '.ip6.arpa';
        }

        throw new Exception\MissingEnvironmentValueException(Placeholder::ML_IP);
    }
}
