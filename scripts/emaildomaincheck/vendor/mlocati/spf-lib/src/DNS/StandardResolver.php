<?php

declare(strict_types=1);

namespace SPFLib\DNS;

use Closure;
use IPLib\Address\AddressInterface;
use IPLib\Factory;
use MLocati\IDNA\DomainName;
use MLocati\IDNA\Exception\Exception as IDNAException;
use SPFLib\Exception\DNSResolutionException;

/**
 * A DNS resolver that uses the dns_get_record() PHP function.
 */
class StandardResolver implements Resolver
{
    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\DNS\Resolver::getTXTRecords()
     */
    public function getTXTRecords(string $domain): array
    {
        $error = 'Unknown error';
        $records = $this->callWithErrorHandler(function () use ($domain) {
            return dns_get_record($this->normalizeDomain($domain), DNS_TXT);
        }, $error);
        if ($records === false) {
            throw new DNSResolutionException($domain, "Failed to get the TXT records for {$domain}: {$error}");
        }
        $result = [];
        foreach ($records as $record) {
            if (isset($record['txt'])) {
                $result[] = $record['txt'];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\DNS\Resolver::getIPAddressesFromDomainName()
     */
    public function getIPAddressesFromDomainName(string $domain): array
    {
        $error = 'Unknown error';
        $records = $this->callWithErrorHandler(function () use ($domain) {
            return dns_get_record($this->normalizeDomain($domain), DNS_A | DNS_AAAA);
        }, $error);
        if ($records === false) {
            throw new DNSResolutionException($domain, "Failed to get the A/AAAA records for {$domain}: {$error}");
        }
        $result = [];
        foreach ($records as $record) {
            $ip = Factory::addressFromString($record['type'] === 'A' ? $record['ip'] : $record['ipv6']);
            if ($ip !== null) {
                $result[] = $ip;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\DNS\Resolver::getMXRecords()
     */
    public function getMXRecords(string $domain): array
    {
        $error = 'Unknown error';
        $records = $this->callWithErrorHandler(function () use ($domain) {
            return dns_get_record($this->normalizeDomain($domain), DNS_MX);
        }, $error);
        if ($records === false) {
            throw new DNSResolutionException($domain, "Failed to get the A/AAAA records for {$domain}: {$error}");
        }
        $result = [];
        foreach ($records as $record) {
            if ($record['type'] === 'MX') {
                $result[] = $record['target'];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\DNS\Resolver::getPTRRecords()
     */
    public function getPTRRecords(string $domain): array
    {
        $error = 'Unknown error';
        $records = $this->callWithErrorHandler(static function () use ($domain) {
            return dns_get_record($domain, DNS_PTR);
        }, $error);
        if ($records === false) {
            throw new DNSResolutionException($domain, "Failed to get the PTR records for {$domain}: {$error}");
        }
        $results = [];
        foreach ($records as $record) {
            $results[] = $record['target'];
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\DNS\Resolver::getDomainNameFromIPAddress()
     */
    public function getDomainNameFromIPAddress(AddressInterface $ip): string
    {
        $result = $this->callWithErrorHandler(static function () use ($ip) {
            return gethostbyaddr((string) $ip);
        });

        return is_string($result) && $result !== (string) $ip ? $result : '';
    }

    /**
     * @return mixed the result of calling $closure
     */
    protected function callWithErrorHandler(Closure $closure, string &$error = '')
    {
        set_error_handler(
            static function ($errno, $errstr) use (&$error): void {
                $error = (string) $errstr;
                if ($error === '') {
                    $error = "Unknown error (code: {$errno})";
                }
            },
            -1
        );
        try {
            $result = $closure();
        } finally {
            restore_error_handler();
        }

        return $result;
    }

    /**
     * @throws \SPFLib\Exception\DNSResolutionException
     */
    protected function normalizeDomain(string $domain): string
    {
        try {
            $actualDomain = DomainName::fromName($domain)->getPunycode();
        } catch (IDNAException $x) {
            $actualDomain = $domain;
        }
        if ($actualDomain === '' || trim($actualDomain) !== $actualDomain) {
            throw new DNSResolutionException($domain, "The domain '{$domain}' is not valid");
        }

        return $actualDomain;
    }
}
