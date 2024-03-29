<?php

declare(strict_types=1);

namespace SPFLib;

use IPLib\Address\IPv4;
use IPLib\Address\IPv6;
use SPFLib\DNS\Resolver;
use SPFLib\DNS\StandardResolver;
use SPFLib\Macro\MacroString\Decoder as MacroStringDecoder;
use SPFLib\Term\Mechanism;
use SPFLib\Term\Modifier;

/**
 * Class that decodes SPF records.
 */
class Decoder
{
    /**
     * @var \SPFLib\DNS\Resolver
     */
    private $dnsResolver;

    /**
     * @var \SPFLib\Macro\MacroString\Decoder
     */
    private $macroStringDecoder;

    /**
     * Initialize the instance.
     *
     * @param \SPFLib\DNS\Resolver|null $dnsResolver the DNS resolver to be used (we'll use the default one if NULL)
     * @param \SPFLib\Macro\MacroString\Decoder|null $macroStringDecoder the decoder to be used to decode macro-strings (we'll use the default one if NULL)
     */
    public function __construct(?Resolver $dnsResolver = null, ?MacroStringDecoder $macroStringDecoder = null)
    {
        $this->dnsResolver = $dnsResolver ?: new StandardResolver();
        $this->macroStringDecoder = $macroStringDecoder ?: MacroStringDecoder::getInstance();
    }

    /**
     * Extract the SPF record associated to a domain.
     *
     * @throws \SPFLib\Exception\DNSResolutionException in case of DNS resolution errors
     * @throws \SPFLib\Exception\MultipleSPFRecordsException if the domain has more that 1 SPF record
     * @throws \SPFLib\Exception\InvalidTermException if the SPF record contains invalid terms
     * @throws \SPFLib\Exception\InvalidMacroStringException if the SPF record contains a term with an invalid macro-string
     *
     * @return \SPFLib\Record|null return NULL if no SPF record has been found
     *
     * @see https://tools.ietf.org/html/rfc7208#section-4.5
     */
    public function getRecordFromDomain(string $domain): ?Record
    {
        $rawSpfRecords = [];
        $txtRecords = $this->getDNSResolver()->getTXTRecords($domain);
        foreach ($txtRecords as $txtRecord) {
            if (strcasecmp($txtRecord, Record::PREFIX) === 0 || stripos($txtRecord, Record::PREFIX . ' ') === 0) {
                $rawSpfRecords[] = $txtRecord;
            }
        }
        switch (count($rawSpfRecords)) {
            case 0:
                return null;
            case 1:
                return $this->getRecordFromTXT($rawSpfRecords[0]);
            default:
                throw new Exception\MultipleSPFRecordsException($domain, $rawSpfRecords);
        }
    }

    /**
     * Parse a TXT record and extract the SPF data.
     *
     * @throws \SPFLib\Exception\InvalidTermException if the SPF record contains invalid terms
     * @throws \SPFLib\Exception\InvalidMacroStringException if the SPF record contains a term with an invalid macro-string
     *
     * @return \SPFLib\Record|null return NULL if $txtRecord is not an SPF record
     *
     * @see https://tools.ietf.org/html/rfc7208#section-4.5
     */
    public function getRecordFromTXT(string $txtRecord): ?Record
    {
        $rawTerms = explode(' ', rtrim($txtRecord, ' '));
        $version = array_shift($rawTerms);
        if (strcasecmp($version, Record::PREFIX) !== 0) {
            return null;
        }
        $record = new Record();
        foreach ($rawTerms as $rawTerm) {
            if ($rawTerm !== '') {
                $record->addTerm($this->parseTerm($rawTerm));
            }
        }

        return $record;
    }

    /**
     * Get the DNS resolver to be used.
     */
    public function getDNSResolver(): Resolver
    {
        return $this->dnsResolver;
    }

    /**
     * Get the MacroString decoder to be used.
     */
    public function getMacroStringDecoder(): MacroStringDecoder
    {
        return $this->macroStringDecoder;
    }

    /**
     * @throws \SPFLib\Exception\InvalidTermException
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseTerm(string $rawTerm): Term
    {
        try {
            $rxQualifier = '(' . implode('|', [
                preg_quote(Mechanism::QUALIFIER_PASS, '/'),
                preg_quote(Mechanism::QUALIFIER_FAIL, '/'),
                preg_quote(Mechanism::QUALIFIER_SOFTFAIL, '/'),
                preg_quote(Mechanism::QUALIFIER_NEUTRAL, '/'),
            ]) . ')';
            $rxMechanism = '(' . implode('|', [
                preg_quote(Mechanism\AllMechanism::HANDLE, '/'),
                preg_quote(Mechanism\IncludeMechanism::HANDLE, '/'),
                preg_quote(Mechanism\AMechanism::HANDLE, '/'),
                preg_quote(Mechanism\MxMechanism::HANDLE, '/'),
                preg_quote(Mechanism\PtrMechanism::HANDLE, '/'),
                preg_quote(Mechanism\Ip4Mechanism::HANDLE, '/'),
                preg_quote(Mechanism\Ip6Mechanism::HANDLE, '/'),
                preg_quote(Mechanism\ExistsMechanism::HANDLE, '/'),
            ]) . ')';
            $rx = '/^' . implode('', [
                "(?P<qualifier>{$rxQualifier})?",
                "(?P<handle>{$rxMechanism})",
                '(?P<data>[:\/].*)?',
            ]) . '$/i';
            $matches = null;
            if (preg_match($rx, $rawTerm, $matches)) {
                $mechanism = $this->parseMechanism($matches['handle'], $matches['qualifier'], $matches['data'] ?? '');
                if ($mechanism !== null) {
                    return $mechanism;
                }
            } else {
                $rxUnknownModifierName = '([A-Za-z][\w\-.]*)';
                $rxModifier = '(' . implode('|', [
                    preg_quote(Modifier\RedirectModifier::HANDLE, '/'),
                    preg_quote(Modifier\ExpModifier::HANDLE, '/'),
                    $rxUnknownModifierName,
                ]) . ')';
                $rx = '/^' . implode('', [
                    "(?P<handle>{$rxModifier})",
                    '=',
                    '(?P<data>.*)?',
                ]) . '$/i';
                if (preg_match($rx, $rawTerm, $matches)) {
                    $modifier = $this->parseModifier($matches['handle'], $matches['data'] ?? '');
                    if ($modifier !== null) {
                        return $modifier;
                    }
                }
            }
        } catch (Exception\InvalidMacroStringException $x) {
            throw $x->setTerm($rawTerm);
        }

        throw new Exception\InvalidTermException($rawTerm);
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseMechanism(string $handle, string $qualifier, string $data): ?Mechanism
    {
        if ($qualifier === '') {
            $qualifier = Mechanism::QUALIFIER_PASS;
        }
        switch (strtolower($handle)) {
            case Mechanism\AllMechanism::HANDLE:
                return $this->parseAllMechanism($qualifier, $data);
            case Mechanism\IncludeMechanism::HANDLE:
                return $this->parseIncludeMechanism($qualifier, $data);
            case Mechanism\AMechanism::HANDLE:
                return $this->parseAMechanism($qualifier, $data);
            case Mechanism\MxMechanism::HANDLE:
                return $this->parseMxMechanism($qualifier, $data);
            case Mechanism\PtrMechanism::HANDLE:
                return $this->parsePtrMechanism($qualifier, $data);
            case Mechanism\Ip4Mechanism::HANDLE:
                return $this->parseIp4Mechanism($qualifier, $data);
            case Mechanism\Ip6Mechanism::HANDLE:
                return $this->parseIp6Mechanism($qualifier, $data);
            case Mechanism\ExistsMechanism::HANDLE:
                return $this->parseExistsMechanism($qualifier, $data);
        }
    }

    protected function parseAllMechanism(string $qualifier, string $data): ?Mechanism\AllMechanism
    {
        if ($data !== '') {
            return null;
        }

        return new Mechanism\AllMechanism($qualifier);
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseIncludeMechanism(string $qualifier, string $data): ?Mechanism\IncludeMechanism
    {
        if ($data === '' || $data[0] !== ':' || $data === ':') {
            return null;
        }

        return new Mechanism\IncludeMechanism(
            $qualifier,
            $this->getMacroStringDecoder()->decode(substr($data, 1))
        );
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseAMechanism(string $qualifier, string $data): ?Mechanism\AMechanism
    {
        $parsed = $this->extractDomainSpecDualCidr($data);
        if ($parsed === null) {
            return null;
        }

        return new Mechanism\AMechanism($qualifier, $parsed[0], $parsed[1], $parsed[2]);
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseMxMechanism(string $qualifier, string $data): ?Mechanism\MxMechanism
    {
        $parsed = $this->extractDomainSpecDualCidr($data);
        if ($parsed === null) {
            return null;
        }

        return new Mechanism\MxMechanism($qualifier, $parsed[0], $parsed[1], $parsed[2]);
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parsePtrMechanism(string $qualifier, string $data): ?Mechanism\PtrMechanism
    {
        $domainSpec = null;
        if ($data !== '') {
            if ($data[0] !== ':' || $data === ':') {
                return null;
            }
            $domainSpec = $this->getMacroStringDecoder()->decode(substr($data, 1));
        }

        return new Mechanism\PtrMechanism($qualifier, $domainSpec);
    }

    protected function parseIp4Mechanism(string $qualifier, string $data): ?Mechanism\Ip4Mechanism
    {
        if ($data === '' || $data[0] !== ':') {
            return null;
        }
        $data = substr($data, 1);
        $matches = null;
        if (preg_match('_^(.+)/(0|([1-9]\d?))$_', $data, $matches)) {
            $cidr = (int) $matches[2];
            if ($cidr > 32) {
                return null;
            }
            $data = $matches[1];
        } else {
            $cidr = null;
        }
        $ip = IPv4::fromString($data, false);
        if ($ip === null) {
            return null;
        }

        return new Mechanism\Ip4Mechanism($qualifier, $ip, $cidr);
    }

    protected function parseIp6Mechanism(string $qualifier, string $data): ?Mechanism\Ip6Mechanism
    {
        if ($data === '' || $data[0] !== ':') {
            return null;
        }
        $data = substr($data, 1);
        $matches = null;
        if (preg_match('_^(.+)/(0|([1-9]\d{0,2}))$_', $data, $matches)) {
            $cidr = (int) $matches[2];
            if ($cidr > 128) {
                return null;
            }
            $data = $matches[1];
        } else {
            $cidr = null;
        }
        $ip = IPv6::fromString($data, false, false);
        if ($ip === null) {
            return null;
        }

        return new Mechanism\Ip6Mechanism($qualifier, $ip, $cidr);
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseExistsMechanism(string $qualifier, string $data): ?Mechanism\ExistsMechanism
    {
        if ($data === '' || $data[0] !== ':' || $data === ':') {
            return null;
        }

        return new Mechanism\ExistsMechanism(
            $qualifier,
            $this->getMacroStringDecoder()->decode(substr($data, 1))
        );
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function extractDomainSpecDualCidr(string $data): ?array
    {
        $domainSpec = null;
        $ip4CidrLength = null;
        $ip6CidrLength = null;
        $matches = null;
        if (preg_match('_^:(.*?)((?://?\d+)*)$_', $data, $matches)) {
            $domainSpecString = $matches[1];
            if ($domainSpecString === '') {
                return null;
            }
            $domainSpec = $this->getMacroStringDecoder()->decode($domainSpecString);
            $data = $matches[2];
        }
        if ($data !== '') {
            $slashPosition = strpos($data, '/');
            if ($slashPosition !== 0) {
                return null;
            }
            if ($slashPosition !== false) {
                $matches = null;
                $data = substr($data, $slashPosition);
                while ($data !== '') {
                    if (!preg_match('_^/(/)?(0|([1-9]\d{0,2}))_', $data, $matches)) {
                        return null;
                    }
                    $num = (int) $matches[2];
                    if ($matches[1] === '/') {
                        if ($num > 128 || $ip6CidrLength !== null) {
                            return null;
                        }
                        $ip6CidrLength = $num;
                    } else {
                        if ($num > 32 || $ip4CidrLength !== null) {
                            return null;
                        }
                        $ip4CidrLength = $num;
                    }
                    $data = substr($data, strlen($matches[0]));
                }
            }
        }

        return [$domainSpec, $ip4CidrLength, $ip6CidrLength];
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseModifier(string $handle, string $data): ?Modifier
    {
        try {
            $term = $this->parseTerm($data);
        } catch (Exception $soFarSoGood) {
            $term = null;
        }
        if ($term !== null && !$term instanceof Modifier\UnknownModifier) {
            throw new Exception\InvalidMacroStringException("{$handle}={$data}", 0, 'The modifier "{$handle}" as a value that indicates that the syntax is probably wrong');
        }
        switch (strtolower($handle)) {
            case Modifier\RedirectModifier::HANDLE:
                return $this->parseRedirectModifier($data);
            case Modifier\ExpModifier::HANDLE:
                return $this->parseExpModifier($data);
            default:
                return $this->parseUnknownModifier($handle, $data);
        }
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseRedirectModifier(string $data): ?Modifier\RedirectModifier
    {
        if ($data === '') {
            return null;
        }

        return new Modifier\RedirectModifier(
            $this->getMacroStringDecoder()->decode($data)
        );
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseExpModifier(string $data): ?Modifier\ExpModifier
    {
        if ($data === '') {
            return null;
        }

        return new Modifier\ExpModifier(
            $this->getMacroStringDecoder()->decode($data)
        );
    }

    /**
     * @throws \SPFLib\Exception\InvalidMacroStringException
     */
    protected function parseUnknownModifier(string $name, string $data): ?Modifier\UnknownModifier
    {
        return new Modifier\UnknownModifier(
            $name,
            $this->getMacroStringDecoder()->decode($data)
        );
    }
}
