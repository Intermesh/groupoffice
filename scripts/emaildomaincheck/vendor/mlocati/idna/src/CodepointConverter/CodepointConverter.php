<?php

namespace MLocati\IDNA\CodepointConverter;

use MLocati\IDNA\Exception\InvalidCharacter;
use MLocati\IDNA\Exception\InvalidCodepoint;
use MLocati\IDNA\Exception\InvalidString;

/**
 * Convert an Unicode Code Point to/from an character.
 */
abstract class CodepointConverter implements CodepointConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::isCodepointValid()
     */
    public function isCodepointValid($codepoint)
    {
        return $this->parseCodepoint($codepoint) !== null;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::characterToCodepoint()
     */
    public function characterToCodepoint($character)
    {
        $char = null;
        if (is_object($character) && is_callable(array($character, '__toString'))) {
            $char = (string) $character;
        } elseif (is_string($character)) {
            $char = $character;
        }
        if ($char === null || $char === '') {
            throw new InvalidCharacter($character);
        }

        return $this->characterToCodepointDo($character);
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::charactersToCodepoints()
     */
    public function charactersToCodepoints(array $characters)
    {
        $result = array();
        foreach ($characters as $character) {
            $result[] = $this->characterToCodepoint($character);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::charactersToString()
     */
    public function charactersToString(array $characters)
    {
        return empty($characters) ? '' : implode('', $characters);
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::codepointToCharacter()
     */
    public function codepointToCharacter($codepoint)
    {
        $int = $this->parseCodepoint($codepoint);
        if ($int === null) {
            throw new InvalidCodepoint($codepoint);
        }

        return $this->codepointToCharacterDo($codepoint);
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::codepointsToCharacters()
     */
    public function codepointsToCharacters(array $codepoints)
    {
        $result = array();
        foreach ($codepoints as $codepoint) {
            $result[] = $this->codepointToCharacter($codepoint);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::codepointsToString()
     */
    public function codepointsToString(array $codepoints)
    {
        $result = '';
        foreach ($codepoints as $codepoint) {
            $result .= $this->codepointToCharacter($codepoint);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::stringToCharacters()
     *
     * @virtual
     */
    public function stringToCharacters($string)
    {
        $result = array();
        $string = (string) $string;
        $numBytes = strlen($string);
        $minBytesPerCharacter = $this->getMinBytesPerCharacter();
        $maxBytesPerCharacter = $this->getMaxBytesPerCharacter();
        for ($start = 0; $start < $numBytes;) {
            $character = null;
            $maxChunkLength = min($maxBytesPerCharacter, $numBytes - $start);
            if ($maxChunkLength >= $minBytesPerCharacter) {
                for ($chunkLength = $minBytesPerCharacter; $chunkLength <= $maxChunkLength; ++$chunkLength) {
                    try {
                        $chunk = substr($string, $start, $chunkLength);
                        $this->characterToCodepoint($chunk);
                        $character = $chunk;
                        $start += $chunkLength;
                        break;
                    } catch (InvalidCharacter $x) {
                    }
                }
            }
            if ($character === null) {
                throw new InvalidString($string);
            }
            $result[] = $character;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \MLocati\IDNA\CodepointConverter\CodepointConverterInterface::stringToCodepoints()
     */
    public function stringToCodepoints($string)
    {
        $result = array();
        foreach ($this->stringToCharacters($string) as $character) {
            try {
                $result[] = $this->characterToCodepoint($character);
            } catch (InvalidCharacter $x) {
                throw new InvalidString($string);
            }
        }

        return $result;
    }

    /**
     * Parse a variable and return a Code Point if it's valid, NULL otherwise.
     *
     * @param int|mixed $codepoint
     *
     * @return int|null
     */
    protected function parseCodepoint($codepoint)
    {
        $int = null;
        switch (gettype($codepoint)) {
            case 'integer':
                $int = $codepoint;
                break;
            case 'string':
                if (is_numeric($codepoint)) {
                    $int = (int) $codepoint;
                }
                break;
        }
        $result = null;
        if ($int !== null) {
            if ($int >= 0 && $int <= static::MAX_CODEPOINT) {
                if ($int < 0x0080 || $int > 0x009F) { // Latin-1 Supplement (AKA C1 Controls)
                    if ($int < 0xD800 || $int > 0xDFFF) { // Surrogates
                        $result = $int;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get the minimum number of bytes per character.
     *
     * @return int
     */
    abstract protected function getMinBytesPerCharacter();

    /**
     * Get the maximum number of bytes per character.
     *
     * @return int
     */
    abstract protected function getMaxBytesPerCharacter();

    /**
     * Encoding-specific code to convert a Unicode Code Point to a character.
     *
     * @param int $codepoint Non-negative integer
     *
     * @throws \MLocati\IDNA\Exception\InvalidCodepoint
     *
     * @return string
     */
    abstract protected function codepointToCharacterDo($codepoint);

    /**
     * Encoding-specific code to convert a character to a Unicode Code Point.
     *
     * @param string $character Non-empty string
     *
     * @throws \MLocati\IDNA\Exception\InvalidCharacter
     *
     * @return int
     */
    abstract protected function characterToCodepointDo($character);
}
