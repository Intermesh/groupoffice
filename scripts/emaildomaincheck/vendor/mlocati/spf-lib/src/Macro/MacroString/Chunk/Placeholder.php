<?php

declare(strict_types=1);

namespace SPFLib\Macro\MacroString\Chunk;

use SPFLib\Macro\MacroString\Chunk;

/**
 * Class that represents a placeholder part of a MacroString.
 */
class Placeholder extends Chunk
{
    /**
     * Placeholder identifier: the email address used in "MAIL FROM" or "HELO".
     *
     * @var string
     */
    public const ML_SENDER = 's';

    /**
     * Placeholder identifier: local-part of the email address used in "MAIL FROM" or "HELO".
     *
     * @var string
     */
    public const ML_SENDER_LOCAL_PART = 'l';

    /**
     * Placeholder identifier: domain of the email address used in "MAIL FROM" or "HELO".
     *
     * @var string
     */
    public const ML_SENDER_DOMAIN = 'o';

    /**
     * Placeholder identifier: the domain that contains the current SPF record (initially, the domain portion of "MAIL FROM" or "HELO").
     *
     * @var string
     */
    public const ML_DOMAIN = 'd';

    /**
     * Placeholder identifier: the IP address of the SMTP client that is emitting the mail, either IPv4 or IPv6.
     *
     * @var string
     */
    public const ML_IP = 'i';

    /**
     * Placeholder identifier: the validated domain name of the IP address of the SMTP client that is emitting the mail (do not use).
     *
     * @var string
     */
    public const ML_IP_VALIDATED_DOMAIN = 'p';

    /**
     * Placeholder identifier: the string "in-addr" if the IP address of the SMTP client that is emitting the mail is ipv4, or "ip6" if it's ipv6.
     *
     * @var string
     */
    public const ML_IP_TYPE = 'v';

    /**
     * Placeholder identifier: HELO/EHLO domain.
     *
     * @var string
     */
    public const ML_HELO_DOMAIN = 'h';

    /**
     * Placeholder identifier: SMTP client IP (easily readable format)
     * Note: only in "exp" text.
     *
     * @var string
     */
    public const ML_SMTP_CLIENT_IP = 'c';

    /**
     * Placeholder identifier: domain name of host performing the check
     * Note: only in "exp" text.
     *
     * @var string
     */
    public const ML_CHECKER_DOMAIN = 'r';

    /**
     * Placeholder identifier: current timestamp
     * Note: only in "exp" text.
     *
     * @var string
     */
    public const ML_CURRENT_TIMESTAMP = 't';

    /**
     * The placeholder identifier (the value of one of the Placeholder::ML_... constants, case-insensitive)).
     *
     * @var string
     */
    private $macroLetter;

    /**
     * The number of parts of the output, after applying the environment values, splitting by $delimiters (NULL or greater than 0).
     *
     * @var int|null
     */
    private $numOutputParts;

    /**
     * Should the output be reversed?
     *
     * @var bool
     */
    private $reverse;

    /**
     * The character(s) to be used to split the output (if empty, we'll assume '.').
     * It can be one of: '.' / '-' / '+' / ',' / '/' / '_' / '='.
     *
     * @var string
     */
    private $delimiters;

    /**
     * Initialize the instance.
     *
     * @param string $macroLetter the placeholder identifier (the value of one of the Placeholder::ML_... constants, case-insensitive)
     * @param int|null $numOutputParts the number of parts of the output, after applying the environment values, splitting by $delimiters (NULL or greater than 0)
     * @param bool $reverse Should the output be reversed?
     * @param string $delimiters the character(s) to be used to split the output (if empty, we'll assume '.', it can be one of: '.' / '-' / '+' / ',' / '/' / '_' / '=')
     */
    public function __construct(string $macroLetter, ?int $numOutputParts = null, bool $reverse = false, string $delimiters = '')
    {
        $this->macroLetter = $macroLetter;
        $this->numOutputParts = $numOutputParts;
        $this->reverse = $reverse;
        $this->delimiters = $delimiters;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Macro\MacroString\Chunk::__toString()
     */
    public function __toString(): string
    {
        return implode('', [
            '%{',
            $this->getMacroLetter(),
            (string) ($this->getNumOutputParts() ?: ''),
            $this->isReverse() ? 'r' : '',
            $this->getDelimiters(),
            '}',
        ]);
    }

    /**
     * Get the placeholder identifier (the value of one of the Placeholder::ML_... constants, case-insensitive).
     */
    public function getMacroLetter(): string
    {
        return $this->macroLetter;
    }

    /**
     * Get the number of parts of the output, after applying the environment values, splitting by $delimiters (NULL or greater than 0).
     */
    public function getNumOutputParts(): ?int
    {
        return $this->numOutputParts;
    }

    /**
     * Should the output be reversed?
     */
    public function isReverse(): bool
    {
        return $this->reverse;
    }

    /**
     * Get the character(s) to be used to split the output (if empty, we'll assume '.').
     * It can zero characters or more of: '.' / '-' / '+' / ',' / '/' / '_' / '='.
     */
    public function getDelimiters(): string
    {
        return $this->delimiters;
    }
}
