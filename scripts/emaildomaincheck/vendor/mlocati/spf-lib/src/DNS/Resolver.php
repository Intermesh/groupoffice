<?php

declare(strict_types=1);

namespace SPFLib\DNS;

use IPLib\Address\AddressInterface;

/**
 * Interface that DNS record resolvers must implement.
 */
interface Resolver
{
    /**
     * Get the TXT records for a domain.
     *
     * @throws \SPFLib\Exception\DNSResolutionException in case of DNS resolution errors
     *
     * @return string[]
     */
    public function getTXTRecords(string $domain): array;

    /**
     * Get the IP addresses associated to a domain name.
     *
     * @throws \SPFLib\Exception\DNSResolutionException in case of DNS resolution errors
     *
     * @return \IPLib\Address\AddressInterface[]
     */
    public function getIPAddressesFromDomainName(string $domain): array;

    /**
     * Get the IP addresses/domain names of the MX DNS record for a domain.
     *
     * @throws \SPFLib\Exception\DNSResolutionException in case of DNS resolution errors
     *
     * @return string[]
     */
    public function getMXRecords(string $domain): array;

    /**
     * Get the value of the PTR DNS records for a domain.
     *
     * @throws \SPFLib\Exception\DNSResolutionException in case of DNS resolution errors
     *
     * @return string[]
     */
    public function getPTRRecords(string $domain): array;

    /**
     * Get the domain name associated to an IP address by performing a reverse IP lookup.
     *
     * @return string empty string in case of failure
     */
    public function getDomainNameFromIPAddress(AddressInterface $ip): string;
}
