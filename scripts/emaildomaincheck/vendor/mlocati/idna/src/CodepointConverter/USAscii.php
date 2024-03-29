<?php

namespace MLocati\IDNA\CodepointConverter;

use MLocati\IDNA\Exception\InvalidCharacter;
use MLocati\IDNA\Exception\InvalidCodepoint;
use MLocati\IDNA\Exception\InvalidString;

/**
 * Convert an Unicode Code Point to/from an character in US-ASCII encoding.
 */
class USAscii extends CodepointConverter
{
    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverter::getMinBytesPerCharacter()
     */
    protected function getMinBytesPerCharacter()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverter::getMaxBytesPerCharacter()
     */
    protected function getMaxBytesPerCharacter()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverter::codepointToCharacterDo()
     */
    protected function codepointToCharacterDo($codepoint)
    {
        if ($codepoint > 0x7F) {
            throw new InvalidCodepoint($codepoint);
        }

        return chr($codepoint);
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverter::characterToCodepointDo()
     */
    protected function characterToCodepointDo($character)
    {
        $result = null;
        if (!isset($character[1])) {
            $byte = ord($character[0]);
            if ($byte <= 0x7F) {
                $result = $byte;
            }
        }
        if ($result === null) {
            throw new InvalidCharacter($character);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverter::stringToCharacters()
     */
    public function stringToCharacters($string)
    {
        $string = (string) $string;
        $result = array();
        if ($string !== '') {
            foreach (str_split($string) as $character) {
                if (ord($character) > 0x7F) {
                    throw new InvalidString($string);
                }
                $result[] = $character;
            }
        }

        return $result;
    }
}
