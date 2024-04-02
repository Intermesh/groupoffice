<?php

namespace MLocati\IDNA\Exception;

/**
 * Exception thrown when an invalid string is met.
 */
class InvalidString extends Exception
{
    /**
     * The invalid string.
     *
     * @var mixed
     */
    protected $string;

    /**
     * @param mixed $string The invalid string
     */
    public function __construct($string)
    {
        $this->string = $string;
        $message = 'Invalid string';
        $str = static::stringifyVariable($string);
        if ($str !== '') {
            $message .= ': '.$str;
        }
        parent::__construct($message);
    }

    /**
     * Get the invalid string.
     *
     * @return mixed
     */
    public function getString()
    {
        return $this->string;
    }
}
