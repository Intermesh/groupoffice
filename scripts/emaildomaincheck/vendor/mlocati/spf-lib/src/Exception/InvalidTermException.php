<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;

/**
 * Exception thrown when an SPF record contains an unrecognized term.
 */
class InvalidTermException extends Exception
{
    /**
     * The term that wasn't recognized.
     *
     * @var string
     */
    protected $term;

    /**
     * Initialize the instance.
     *
     * @param string $term the term that wasn't recognized
     */
    public function __construct(string $term, ?string $message = null)
    {
        parent::__construct($message === null ? "The SPF record contains an unrecognized term: {$term}" : $message);
        $this->term = $term;
    }

    /**
     * Get the term that wasn't recognized.
     */
    public function getTerm(): string
    {
        return $this->term;
    }
}
