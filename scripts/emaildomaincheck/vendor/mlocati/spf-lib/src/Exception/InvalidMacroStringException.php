<?php

declare(strict_types=1);

namespace SPFLib\Exception;

/**
 * Exception thrown when an SPF record contains an term with an invalid macro-string.
 */
class InvalidMacroStringException extends InvalidTermException
{
    /**
     * The invalid macro-string.
     *
     * @var string
     */
    private $macroString;

    /**
     * The position inside the macro-string when the error occurred.
     *
     * @var int
     */
    private $macroStringPosition;

    /**
     * Initialize the instance.
     *
     * @param string $macroString the invalid macro-string
     */
    public function __construct(string $macroString, int $macroStringPosition, string $message = '')
    {
        parent::__construct('', $message === '' ? "The macro-string '{$macroString}' is not valid (error found at macro-string position {$macroStringPosition})" : $message);
        $this->macroString = $macroString;
    }

    /**
     * Get the invalid macro-string.
     */
    public function getMacroString(): string
    {
        return $this->macroString;
    }

    /**
     * Get the position inside the macro-string when the error occurred.
     */
    public function getMacroStringPosition(): int
    {
        return $this->macroStringPosition;
    }

    /**
     * Set the term that wasn't recognized.
     *
     * @return $this
     */
    public function setTerm(string $value): self
    {
        $this->term = $value;

        return $this;
    }
}
