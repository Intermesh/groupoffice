<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Check\Result;
use SPFLib\Exception;
use SPFLib\Term\Mechanism\IncludeMechanism;

/**
 * Exception thrown during the check process of an include mechanism to signal that further processing should be stopped.
 */
class IncludeMechanismException extends Exception
{
    /**
     * The final result code to be returned (the value of one of the Result::CODE_... constants).
     *
     * @var string
     */
    private $finalResultCode;

    /**
     * The domain owning the "include" spf mechanism.
     *
     * @var string
     */
    private $domain;

    /**
     * The "include" mechanism for which the exception has been thrown.
     *
     * @var \SPFLib\Term\Mechanism\IncludeMechanism $mechanism
     */
    private $mechanism;

    /**
     * The problematic result of the "include" mechanism.
     *
     * @var \SPFLib\Check\Result $includeResult
     */
    private $includeResult;

    /**
     * Initialize the instance.
     *
     * @param string $finalResultCode the final result code to be returned (the value of one of the Result::CODE_... constants).
     * @param string $domain the domain owning the "include" spf mechanism
     * @param \SPFLib\Term\Mechanism\IncludeMechanism $mechanism the "include" mechanism for which the exception has been thrown
     * @param \SPFLib\Check\Result $includeResult the problematic result of the "include" mechanism
     */
    public function __construct(string $finalResultCode, string $domain, IncludeMechanism $mechanism, Result $includeResult)
    {
        parent::__construct("Set '{$finalResultCode}' final result by include mechanism");
        $this->finalResultCode = $finalResultCode;
        $this->domain = $domain;
        $this->mechanism = $mechanism;
        $this->includeResult = $includeResult;
    }

    /**
     * Get the final result code to be returned (the value of one of the Result::CODE_...
     */
    public function getFinalResultCode(): string
    {
        return $this->finalResultCode;
    }

    /**
     * Get the domain owning the "include" spf mechanism.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get the "include" mechanism for which the exception has been thrown.
     */
    public function getMechanism(): IncludeMechanism
    {
        return $this->mechanism;
    }

    /**
     * Get the problematic result of the "include" mechanism.
     */
    public function getIncludeResult(): Result
    {
        return $this->includeResult;
    }
}
