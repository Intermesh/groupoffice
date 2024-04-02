<?php

declare(strict_types=1);

namespace SPFLib\Semantic;

use SPFLib\Record;

/**
 * Class that represent a semantic issue reported by OnlineSemanticVamidator.
 */
class OnlineIssue extends AbstractIssue
{
    /**
     * Semantic warning code: neither a domain nor a record has been provided.
     *
     * @var int
     */
    public const CODE_NODOMAIN_NORECORD_PROVIDED = 100;

    /**
     * Semantic warning code: a recursive domain has been dected whule parsing included/redirected-to domains.
     *
     * @var int
     */
    public const CODE_RECURSIVE_DOMAIN_DETECTED = 101;

    /**
     * Semantic warning code: a domain doesn't provide an SPF record.
     *
     * @var int
     */
    public const CODE_RECORD_NOT_FOUND = 102;

    /**
     * Semantic warning code: an error occurred while fetching and parsing the SPF record from a domain.
     *
     * @var int
     */
    public const CODE_RECORD_FETCH_OR_PARSE_FAILED = 103;

    /**
     * Semantic warning code: an error occurred while parsing an SPF TXT record.
     *
     * @var int
     */
    public const CODE_RECORD_PARSE_FAILED = 104;

    /**
     * Semantic warning code: too many terms that involve DNS lookups (calculating also the included/redirect-to domains).
     *
     * @var int
     */
    public const CODE_TOO_MANY_DNS_LOOKUPS_ONLINE = 105;

    /**
     * Semantic warning code: unable to parse the SPF record of an include mechanism/redirect modifier since it contains a placeholder.
     *
     * @var int
     */
    public const CODE_DOMAIN_WITH_PLACEHOLDER = 106;

    /**
     * The associated domain where the SPF record has been fetched from (empty string if not available).
     *
     * @var string
     */
    private $domain;

    /**
     * The raw TXT record (empty string if not available).
     *
     * @var string
     */
    private $txtRecord;

    /**
     * The affected record (NULL if not available).
     *
     * @var \SPFLib\Record|null
     */
    private $record;

    /**
     * Initialize the instance.
     *
     * @param string $domain the associated domain where the SPF record has been fetched from (empty string if not available)
     * @param string $txtRecord the raw TXT record (empty string if not available)
     */
    public function __construct(string $domain, string $txtRecord, ?Record $record, int $code, string $description, int $level)
    {
        parent::__construct($code, $description, $level);
        $this->domain = $domain;
        $this->txtRecord = $txtRecord;
        $this->record = $record;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Semantic\AbstractIssue::__toString()
     */
    public function __toString(): string
    {
        $parts = [];
        $level = $this->getLevelDescription();
        if ($level !== '') {
            $parts[] = "[{$level}]";
        }
        $domain = $this->getDomain();
        if ($domain !== '') {
            $parts[] = "[domain: {$domain}]";
        }
        $record = $this->getTxtRecord();
        if ($record !== '') {
            $parts[] = "[record: {$record}]";
        }
        $parts[] = $this->getDescription();

        return implode(' ', $parts);
    }

    /**
     * Create a new instance starting from an offline issue.
     */
    public static function fromOfflineIssue(Issue $offlineIssue, string $domain): self
    {
        return new self($domain, '', $offlineIssue->getRecord(), $offlineIssue->getCode(), $offlineIssue->getDescription(), $offlineIssue->getLevel());
    }

    /**
     * Get the associated domain where the SPF record has been fetched from (empty string if not available).
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get the raw TXT record (empty string if not available).
     */
    public function getTxtRecord(): string
    {
        return $this->txtRecord !== '' ? $this->txtRecord : (string) $this->getRecord();
    }

    /**
     * Get the affected record (NULL if not available).
     */
    public function getRecord(): ?Record
    {
        return $this->record;
    }
}
