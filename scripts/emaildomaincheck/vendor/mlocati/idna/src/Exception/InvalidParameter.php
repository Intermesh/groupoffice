<?php

namespace MLocati\IDNA\Exception;

/**
 * Exception thrown when a parameter is not valid.
 */
class InvalidParameter extends Exception
{
    /**
     * The function/method name.
     *
     * @var string
     */
    protected $function;

    /**
     * The parameter name.
     *
     * @var string
     */
    protected $parameterName;

    /**
     * Initialize the instance.
     *
     * @param string $function the function/method name
     * @param string $parameterName the parameter name
     * @param string $message the optional message describing the error
     */
    public function __construct($function, $parameterName, $message = '')
    {
        $this->function = $function;
        $this->parameterName = $parameterName;
        $message = "Invalid parameter $parameterName in $function".(($message === '') ? '' : ":\n$message");
        parent::__construct($message);
    }

    /**
     * Get the function/method name.
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Get the parameter name.
     *
     * @return string
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }
}
