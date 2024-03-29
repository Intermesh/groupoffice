<?php

namespace MLocati\IDNA\Exception;

/**
 * Exception thrown when a domain name contains some invalid characters.
 */
class InvalidDomainNameCharacters extends Exception
{
    /**
     * The invalid code points found.
     *
     * @var int[]
     */
    protected $codepoints;

    /**
     * The invalid characters. If available they are separated by a newline ("\n"), otherwise it's an empty string.
     *
     * @var string
     */
    protected $characters;

    /**
     * @param int[] $codepoints The invalid code points
     * @param string $characters The invalid characters
     */
    public function __construct(array $codepoints, $characters = '')
    {
        $this->codepoints = $codepoints;
        $this->characters = $characters;
        $num = count($codepoints);
        if (count($codepoints) === 1) {
            $message = 'The domain name contains an invalid character';
        } else {
            $message = "The domain name contains $num invalid characters";
        }
        if ($characters !== '') {
            if ($num === 1) {
                $message .= ": $characters";
            } else {
                $message .= ":\n".$characters;
            }
        }
        parent::__construct($message);
    }

    /**
     * Get the invalid code points.
     *
     * @return int[]
     */
    public function getCodepoints()
    {
        return $this->codepoints;
    }

    /**
     * Get the invalid characters (if available). If available they are separated by a newline ("\n"), otherwise it's an empty string.
     *
     * @return string
     */
    public function getCharacters()
    {
        return $this->characters;
    }
}
