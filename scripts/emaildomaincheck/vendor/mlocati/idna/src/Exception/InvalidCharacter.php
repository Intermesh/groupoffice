<?php

namespace MLocati\IDNA\Exception;

/**
 * Exception thrown when an invalid character is met.
 */
class InvalidCharacter extends Exception
{
    /**
     * The invalid character.
     *
     * @var mixed
     */
    protected $character;

    /**
     * @param mixed $character The invalid character
     */
    public function __construct($character)
    {
        $this->character = $character;
        $message = 'Invalid character';
        $str = static::stringifyVariable($character);
        if ($str !== '') {
            $message .= ': '.$str;
        }
        parent::__construct($message);
    }

    /**
     * Get the invalid character.
     *
     * @return mixed
     */
    public function getCharacter()
    {
        return $this->character;
    }
}
