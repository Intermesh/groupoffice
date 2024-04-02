<?php

declare(strict_types=1);

namespace SPFLib;

use SPFLib\Check\State;
use SPFLib\Semantic\OnlineIssue;
use SPFLib\Term\Mechanism;
use SPFLib\Term\Modifier;

/**
 * An extension of the SemanticValidator that check all the SPF record in the chain.
 */
class OnlineSemanticValidator
{
    /**
     * @var \SPFLib\Decoder
     */
    private $decoder;

    /**
     * @var \SPFLib\SemanticValidator
     */
    private $semanticValidator;

    public function __construct(?Decoder $decoder = null, ?SemanticValidator $semanticValidator = null)
    {
        $this->decoder = $decoder ?: new Decoder();
        $this->semanticValidator = $semanticValidator ?: new SemanticValidator();
    }

    /**
     * Get all the semantical warnings of the SPF record of a domain, parsing also all the included/redirected-to records.
     *
     * @param string $domain the domain to be checked
     * @param int|null $minimumLevel the minimum level of the issues (the value of one of the OnlineIssue::LEVEL_... constants)
     *
     * @return \SPFLib\Semantic\OnlineIssue[] The warnings
     */
    public function validateDomain(string $domain, ?int $minimumLevel = null): array
    {
        return $this->filterLevel($this->validate($domain, null), $minimumLevel);
    }

    /**
     * Get all the semantical warnings of a raw SPF record, parsing also all the included/redirected-to records.
     *
     * @param string $txtRecord the raw SPF record to be checked
     * @param string $domain the domain owning the $txtRecord SFP record
     * @param int|null $minimumLevel the minimum level of the issues (the value of one of the OnlineIssue::LEVEL_... constants)
     *
     * @return \SPFLib\Semantic\OnlineIssue[] The warnings
     */
    public function validateRawRecord(string $txtRecord, string $domain = '', ?int $minimumLevel = null): array
    {
        $issues = null;
        try {
            $record = $this->getDecoder()->getRecordFromTXT($txtRecord);
        } catch (Exception $x) {
            $issues = [new OnlineIssue($domain, $txtRecord, null, OnlineIssue::CODE_RECORD_PARSE_FAILED, $x->getMessage(), OnlineIssue::LEVEL_FATAL)];
        }
        if ($issues === null) {
            if ($record === null) {
                $issues = [new OnlineIssue($domain, $txtRecord, null, OnlineIssue::CODE_RECORD_PARSE_FAILED, "'{$txtRecord}' is not a valid SPF record", OnlineIssue::LEVEL_FATAL)];
            } else {
                $issues = $this->validate($domain, $record);
            }
        }

        return $this->filterLevel($issues, $minimumLevel);
    }

    /**
     * Get all the semantical warnings of a parsed SPF record, parsing also all the included/redirected-to records.
     *
     * @param \SPFLib\Record $record the record to be checked
     * @param string $domain the domain owning the $record SFP record
     * @param int|null $minimumLevel the minimum level of the issues (the value of one of the OnlineIssue::LEVEL_... constants)
     *
     * @return \SPFLib\Semantic\OnlineIssue[] The warnings
     */
    public function validateRecord(Record $record, string $domain = '', ?int $minimumLevel = null): array
    {
        return $this->filterLevel($this->validate($domain, $record), $minimumLevel);
    }

    protected function validate(string $domain, ?Record $record, ?array &$state = null): array
    {
        if ($state === null) {
            $isTopLevel = true;
            $state = ['subRecordsDNSLookupCount' => 0, 'parentParsedDomains' => []];
        } else {
            $isTopLevel = false;
        }
        if ($record === null) {
            if ($domain === '') {
                return [new OnlineIssue($domain, '', null, OnlineIssue::CODE_NODOMAIN_NORECORD_PROVIDED, 'Neither a domain nor an SPF record has been provided.', OnlineIssue::LEVEL_FATAL)];
            }
            if (in_array($domain, $state['parentParsedDomains'], true)) {
                return [new OnlineIssue($domain, '', null, OnlineIssue::CODE_RECURSIVE_DOMAIN_DETECTED, "The domain {$domain} is included/redirected-to recursively", OnlineIssue::LEVEL_FATAL)];
            }
            try {
                $record = $this->getDecoder()->getRecordFromDomain($domain);
            } catch (Exception $x) {
                return [new OnlineIssue($domain, '', null, OnlineIssue::CODE_RECORD_FETCH_OR_PARSE_FAILED, $x->getMessage(), OnlineIssue::LEVEL_FATAL)];
            }
            if ($record === null) {
                return [new OnlineIssue($domain, '', null, OnlineIssue::CODE_RECORD_NOT_FOUND, "No SPF records found for domain {$domain}", OnlineIssue::LEVEL_FATAL)];
            }
        }
        $parentParsedDomains = $state['parentParsedDomains'];
        if ($domain !== '') {
            $state['parentParsedDomains'][] = $domain;
        }
        $result = [];
        foreach ($this->getSemanticValidator()->validate($record, null) as $offlineIssue) {
            $result[] = OnlineIssue::fromOfflineIssue($offlineIssue, $domain);
        }
        foreach ($record->getMechanisms() as $mechanism) {
            if ($mechanism instanceof Mechanism\IncludeMechanism) {
                if ($mechanism->getDomainSpec()->containsPlaceholders()) {
                    $result[] = new OnlineIssue($domain, '', $record, OnlineIssue::CODE_DOMAIN_WITH_PLACEHOLDER, "The mechanism {$mechanism} includes a placeholder: its SPF record has not been parsed.", OnlineIssue::LEVEL_NOTICE);
                } else {
                    $result = array_merge($result, $this->validate((string) $mechanism->getDomainSpec(), null, $state));
                }
            }
        }
        foreach ($record->getModifiers() as $modifier) {
            if ($modifier instanceof Modifier\RedirectModifier) {
                if ($modifier->getDomainSpec()->containsPlaceholders()) {
                    $result[] = new OnlineIssue($domain, '', $record, OnlineIssue::CODE_DOMAIN_WITH_PLACEHOLDER, "The modifier {$modifier} includes a placeholder: its SPF record has not been parsed.", OnlineIssue::LEVEL_NOTICE);
                } else {
                    $result = array_merge($result, $this->validate((string) $modifier->getDomainSpec(), null, $state));
                }
            }
        }
        $state['parentParsedDomains'] = $parentParsedDomains;
        $thisDNSLookupCount = $this->getSemanticValidator()->getDirectDNSLookups($record);
        if ($isTopLevel) {
            $totalDNSLookupCount = $state['subRecordsDNSLookupCount'] + $thisDNSLookupCount;
            $maxQueries = State::MAX_DNS_LOOKUPS;
            if ($totalDNSLookupCount > $maxQueries) {
                $result[] = new OnlineIssue(
                    $domain,
                    '',
                    $record,
                    OnlineIssue::CODE_TOO_MANY_DNS_LOOKUPS_ONLINE,
                    "The total number of the '" . implode("', '", $this->getSemanticValidator()::MECHANISMS_INVOLVING_DNS_LOOKUPS) . "' mechanisms and the '" . implode("', '", $this->getSemanticValidator()::MODIFIERS_INVOLVING_DNS_LOOKUPS) . "' modifiers is {$totalDNSLookupCount} (it should not exceed {$maxQueries})",
                    OnlineIssue::LEVEL_WARNING
                );
            }
        } else {
            $state['subRecordsDNSLookupCount'] += $thisDNSLookupCount;
        }

        return $result;
    }

    protected function getDecoder(): Decoder
    {
        return $this->decoder;
    }

    protected function getSemanticValidator(): SemanticValidator
    {
        return $this->semanticValidator;
    }

    /**
     * @param \SPFLib\Semantic\OnlineIssue[] $issues
     * @param int|null $minimumLevel
     *
     * @return \SPFLib\Semantic\OnlineIssue[]
     */
    protected function filterLevel(array $issues, ?int $minimumLevel): array
    {
        if ($minimumLevel === null) {
            return $issues;
        }

        return array_values(
            array_filter(
                $issues,
                static function (OnlineIssue $issue) use ($minimumLevel): bool {
                    return $issue->getLevel() >= $minimumLevel;
                }
            )
        );
    }
}
