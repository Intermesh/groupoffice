<?php

namespace MLocati\IDNA\CodepointConverter;

/**
 * Convert an Unicode Code Point to/from an character.
 */
interface CodepointConverterInterface
{
    /**
     * The minimum codepoint.
     *
     * @var int
     */
    const MIN_CODEPOINT = 0;

    /**
     * The maximum codepoint.
     *
     * @var int
     */
    const MAX_CODEPOINT = 0x10FFFF;

    /**
     * Check if a variable contains a valid Unicode Code Point.
     *
     * @param int|mixed $codepoint
     *
     * @return bool
     */
    public function isCodepointValid($codepoint);

    /**
     * Convert an UTF8-encoded character to its Unicode Code Point.
     *
     * @param string $character
     *
     * @throws \MLocati\IDNA\Exception\InvalidCharacter
     *
     * @return int
     */
    public function characterToCodepoint($character);

    /**
     * Encode a list of code points to a list of characters.
     *
     * @param string[] $characters
     *
     * @throws \MLocati\IDNA\Exception\InvalidCharacter
     *
     * @return int[]
     */
    public function charactersToCodepoints(array $characters);

    /**
     * Get the string starting from a list of characters.
     *
     * @param string[] $characters
     *
     * @return string
     */
    public function charactersToString(array $characters);

    /**
     * Convert a Unicode Code Point to an character with an implementation-specific encoding.
     *
     * @param int|mixed $codepoint
     *
     * @throws \MLocati\IDNA\Exception\InvalidCodepoint
     *
     * @return string
     */
    public function codepointToCharacter($codepoint);

    /**
     * Encode a list of code points to a list of characters.
     *
     * @param int[] $codepoints
     *
     * @throws \MLocati\IDNA\Exception\InvalidCodepoint
     *
     * @return string[]
     */
    public function codepointsToCharacters(array $codepoints);

    /**
     * Encode a list of code points to a string.
     *
     * @param int[] $codepoints
     *
     * @throws \MLocati\IDNA\Exception\InvalidCodepoint
     *
     * @return string
     */
    public function codepointsToString(array $codepoints);

    /**
     * Get the character list of a string.
     *
     * @param string $string
     *
     * @throws \MLocati\IDNA\Exception\InvalidString
     *
     * @return string[]
     */
    public function stringToCharacters($string);

    /**
     * Get the code point list of a string.
     *
     * @param string $string
     *
     * @throws \MLocati\IDNA\Exception\InvalidString
     *
     * @return int[]
     */
    public function stringToCodepoints($string);
}
