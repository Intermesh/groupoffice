<?php

declare(strict_types=1);

namespace SPFLib\Semantic;

/**
 * Class that represent a semantic issue reported by SemanticVamidator or by OnlineSemanticVamidator.
 */
abstract class AbstractIssue
{
    /**
     * Semantic warning code: too many terms that involve DNS lookups.
     *
     * @var int
     */
    public const CODE_TOO_MANY_DNS_LOOKUPS = 1;

    /**
     * Semantic warning code: 'all' should be the last mechanism.
     *
     * @var int
     */
    public const CODE_ALL_NOT_LAST_MECHANISM = 2;

    /**
     * Semantic warning code: both the 'all' mechanism and the 'redirect' modifier are present ('redirect' will be ignored).
     *
     * @var int
     */
    public const CODE_ALL_AND_REDIRECT = 3;

    /**
     * Semantic warning code: the 'ptr' mechanism should be avoided (it's slow, expensive, unreliable).
     *
     * @var int
     */
    public const CODE_SHOULD_AVOID_PTR = 4;

    /**
     * Semantic warning code: the 'redirect' and 'exp' modifiers should appear after mechanisms.
     *
     * @var int
     */
    public const CODE_MODIFIER_NOT_AFTER_MECHANISMS = 5;

    /**
     * Semantic warning code: the 'redirect' and 'exp' modifiers can't appear more that 1 time.
     *
     * @var int
     */
    public const CODE_DUPLICATED_MODIFIER = 6;

    /**
     * Semantic warning code: unknown modifier specified.
     *
     * @var int
     */
    public const CODE_UNKNOWN_MODIFIER = 7;

    /**
     * Semantic warning code: a macro-string contains the "p" (validated domain) macro-letter, which should be avoided (it's slow, expensive, unreliable).
     *
     * @var int
     */
    public const CODE_SHOULD_AVOID_VALIDATED_DOMAIN_MACRO = 8;

    /**
     * Issue level: notice (can be ignored).
     *
     * @var int
     */
    public const LEVEL_NOTICE = 1;

    /**
     * Issue level: warning (should be fixed).
     *
     * @var int
     */
    public const LEVEL_WARNING = 2;

    /**
     * Issue level: fatal (must be fixed).
     *
     * @var int
     */
    public const LEVEL_FATAL = 3;

    /**
     * The code of the issue (the value of one of the Issue::CODE_... constants).
     *
     * @var int
     */
    private $code;

    /**
     * The issue description.
     *
     * @var string
     */
    private $description;

    /**
     * The issue level (the value of one of the Issue::LEVEL_... constants).
     *
     * @var int
     */
    private $level;

    /**
     * Initialize the instance.
     */
    protected function __construct(int $code, string $description, int $level)
    {
        $this->code = $code;
        $this->description = $description;
        $this->level = $level;
    }

    /**
     * Get a textual representation of this issue.
     */
    abstract public function __toString(): string;

    /**
     * Get the code of the issue (the value of one of the Issue::CODE_... constants).
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Get the issue description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the issue level (the value of one of the Issue::LEVEL_... constants).
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    protected function getLevelDescription(): string
    {
        $level = $this->getLevel();
        switch ($level) {
            case static::LEVEL_NOTICE:
                return 'notice';
            case static::LEVEL_WARNING:
                return 'warning';
            case static::LEVEL_FATAL:
                return 'fatal';
        }

        return (string) $level;
    }
}
