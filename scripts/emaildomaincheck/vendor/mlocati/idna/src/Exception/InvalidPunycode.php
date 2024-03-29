<?php

namespace MLocati\IDNA\Exception;

/**
 * Exception thrown when an invalid punycode is met.
 */
class InvalidPunycode extends Exception
{
    /**
     * The invalid punycode.
     *
     * @var mixed
     */
    protected $punycode;

    /**
     * @param mixed $string The invalid string
     */
    public function __construct($punycode)
    {
        $this->punycode = $punycode;
        $message = 'Invalid punycode';
        $str = static::stringifyVariable($punycode);
        if ($str !== '') {
            $message .= ': '.$str;
        }
        parent::__construct($message);
    }

    /**
     * Get the invalid punycode.
     *
     * @return mixed
     */
    public function getPunycode()
    {
        return $this->punycode;
    }
}
