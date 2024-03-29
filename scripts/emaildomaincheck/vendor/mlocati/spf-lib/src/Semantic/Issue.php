<?php

declare(strict_types=1);

namespace SPFLib\Semantic;

use SPFLib\Record;

/**
 * Class that represent a semantic issue reported by SemanticVamidator.
 */
class Issue extends AbstractIssue
{
    /**
     * The affected record.
     *
     * @var \SPFLib\Record
     */
    private $record;

    /**
     * Initialize the instance.
     */
    public function __construct(Record $record, int $code, string $description, int $level)
    {
        parent::__construct($code, $description, $level);
        $this->record = $record;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Semantic\AbstractIssue::__toString()
     */
    public function __toString(): string
    {
        $level = $this->getLevelDescription();

        return $level === '' ? $this->getDescription() : "[{$level}] {$this->getDescription()}";
    }

    /**
     * Get the affected record.
     */
    public function getRecord(): Record
    {
        return $this->record;
    }
}
