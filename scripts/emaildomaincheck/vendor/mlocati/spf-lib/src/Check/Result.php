<?php

declare(strict_types=1);

namespace SPFLib\Check;

use SPFLib\Term\Mechanism;

/**
 * Class that holds the result of the check operation.
 */
class Result
{
    /**
     * Check result: invalid domain or no SPF record found.
     *
     * @var string
     */
    public const CODE_NONE = 'none';

    /**
     * Check result: the SPF record explicitly stated that it's not asserting that the IP address is authorized or unauthorized.
     *
     * @var string
     */
    public const CODE_NEUTRAL = 'neutral';

    /**
     * Check result: the SPF record stated that the IP address is authorized.
     *
     * @var string
     */
    public const CODE_PASS = 'pass';

    /**
     * Check result: the SPF record stated that the IP address is not authorized for sure.
     *
     * @var string
     */
    public const CODE_FAIL = 'fail';

    /**
     * Check result: the SPF record stated that the IP address is probably not authorized.
     *
     * @var string
     */
    public const CODE_SOFTFAIL = 'softfail';

    /**
     * Check result: a transient (generally DNS) error while performing the check (a later retry may succeed).
     *
     * @var string
     */
    public const CODE_ERROR_TEMPORARY = 'temperror';

    /**
     * Check result: the domain SPF record could not be correctly interpreted (requires DNS operator intervention to be resolved).
     *
     * @var string
     */
    public const CODE_ERROR_PERMANENT = 'permerror';

    /**
     * The result code of the check.
     *
     * @var string the value of one of the Result::CODE_... constants
     */
    private $code;

    /**
     * The mechanism that matched (if applicable).
     *
     * @var \SPFLib\Term\Mechanism|null
     */
    private $matchedMechanism;

    /**
     * A list of messages related to the check process.
     *
     * @var array
     */
    private $messages = [];

    /**
     * The explanation for the "fail" case.
     *
     * @var string
     */
    private $failExplanation = '';

    /**
     * Initialize the instance.
     *
     * @param string $code the value of one of the Result::CODE_... constants
     * @param \SPFLib\Term\Mechanism|null $matchedMechanism the mechanism that matched (if applicable)
     */
    protected function __construct(string $code, ?Mechanism $matchedMechanism = null)
    {
        $this->code = $code;
        $this->matchedMechanism = $matchedMechanism;
    }

    /**
     * Create a new instance.
     *
     * @param string $code the value of one of the Result::CODE_... constants
     * @param \SPFLib\Term\Mechanism|null $matchedMechanism the mechanism that matched (if applicable)
     *
     * @return static
     */
    public static function create(string $code, ?Mechanism $matchedMechanism = null): self
    {
        return new static($code, $matchedMechanism);
    }

    /**
     * Get the result code of the check.
     *
     * @return string the value of one of the Result::CODE_... constants
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get the mechanism that matched (if applicable).
     *
     * @var \SPFLib\Term\Mechanism|null
     */
    public function getMatchedMechanism(): ?Mechanism
    {
        return $this->matchedMechanism;
    }

    /**
     * Get a list of messages met during the check process.
     *
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Add a message.
     *
     * @return $this
     */
    public function addMessage(string $value): self
    {
        $this->messages[] = $value;

        return $this;
    }

    /**
     * Add multiple messages.
     *
     * @param string[] $value
     *
     * @return $this
     */
    public function addMessages(array $value): self
    {
        foreach ($value as $message) {
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * Set the explanation for the "fail" case.
     *
     * @return $this
     */
    public function setFailExplanation(string $value): self
    {
        $this->failExplanation = $value;

        return $this;
    }

    /**
     * Get the explanation for the "fail" case.
     */
    public function getFailExplanation(): string
    {
        return $this->failExplanation;
    }
}
