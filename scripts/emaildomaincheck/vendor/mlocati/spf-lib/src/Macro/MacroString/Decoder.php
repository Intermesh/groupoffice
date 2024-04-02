<?php

declare(strict_types=1);

namespace SPFLib\Macro\MacroString;

use SPFLib\Exception;
use SPFLib\Macro\MacroString;
use SPFLib\Macro\MacroString\Chunk\Placeholder;

/**
 * Class that decodes a string (as present in the DNS record) representing a MacroString.
 */
class Decoder
{
    /**
     * Decoder flag: none.
     *
     * @var int
     */
    public const FLAG_NONE = 0b0000;

    /**
     * Decoder flag: consider an empty string as a valid macro-string.
     *
     * @var int
     */
    public const FLAG_ALLOWEMPTY = 0b0001;

    /**
     * Decoder flag: the MacroString is for the final value of an "exp" modifier.
     *
     * @var int
     */
    public const FLAG_EXP = 0b0010;

    /**
     * @var \SPFLib\Macro\MacroString\Decoder|null
     */
    private static $instance;

    protected function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Deocode a string (as present in the DNS record) representing a MacroString.
     *
     * @throws \SPFLib\Exception\InvalidMacroStringException if the MacroString is not valid
     *
     * @see https://tools.ietf.org/html/rfc7208#section-7.1
     */
    public function decode(string $string, int $flags = self::FLAG_NONE): MacroString
    {
        $result = new MacroString();
        $length = strlen($string);
        if ($length === 0) {
            if (($flags & static::FLAG_ALLOWEMPTY) !== static::FLAG_ALLOWEMPTY) {
                throw new Exception\InvalidMacroStringException($string, 0, "The macro-string can't be empty");
            }

            return $result;
        }
        $placeholderRegex = implode('', [
            '\{',
            '(?P<macroLetter>[' . preg_quote($this->getAllowedPlaceholderChars($flags), '/') . '])',
            '(?P<numOutputParts>\d*)',
            '(?P<reverse>r?)',
            '(?P<delimiters>[' . preg_quote($this->getAllowedDelimiters($flags), '/') . ']*)',
            '\}',
        ]);
        $matches = null;
        $currentLiteralString = '';
        $minLiteralAscii = ($flags & static::FLAG_EXP) ? 0x20 : 0x21;
        for ($index = 0; $index < $length; $index++) {
            $char = $string[$index];
            if ($char === '%') {
                $nextIndex = $index + 1;
                $nextChar = $string[$nextIndex] ?? '';
                switch ($nextChar) {
                    case '%':
                    case '_':
                    case '-':
                        $currentLiteralString .= $char . $nextChar;
                        $index++;
                        continue 2;
                }
                if ($nextChar !== '{') {
                    throw new Exception\InvalidMacroStringException($string, $index, "The macro-string '{$string}' contains a misplaced '%' character at position {$index}");
                }
                $substr = substr($string, $nextIndex);
                if (!preg_match("/^{$placeholderRegex}/i", $substr, $matches)) {
                    throw new Exception\InvalidMacroStringException($string, $index, "The macro-string '{$string}' contains an unrecognized macro-expand string at position {$index}");
                }
                $numOutputParts = $matches['numOutputParts'] === '' ? null : (int) $matches['numOutputParts'];
                if ($numOutputParts === 0) {
                    throw new Exception\InvalidMacroStringException($string, $index, "The macro-string '{$string}' contains a macro-expand string at position {$index} with an invalid number of output parts ({$matches['numOutputParts']})");
                }
                if ($currentLiteralString !== '') {
                    $result->addChunk(new Chunk\LiteralString($currentLiteralString));
                    $currentLiteralString = '';
                }
                $result->addChunk(new Chunk\Placeholder($matches['macroLetter'], $numOutputParts, $matches['reverse'] !== '', $matches['delimiters']));
                $index += strlen($matches[0]);
                continue;
            }
            $ord = ord($char);
            if ($ord < $minLiteralAscii || $ord > 0x7e) {
                throw new Exception\InvalidMacroStringException($string, $index, "The macro-string '{$string}' contains an invalid character (ASCII code: {$ord}) at the position {$index}");
            }
            $currentLiteralString .= $char;
        }
        if ($currentLiteralString !== '') {
            $result->addChunk(new Chunk\LiteralString($currentLiteralString));
        }

        return $result;
    }

    public function getAllowedPlaceholderChars(int $flags): string
    {
        $list = implode('', [
            Placeholder::ML_SENDER,
            Placeholder::ML_SENDER_LOCAL_PART,
            Placeholder::ML_SENDER_DOMAIN,
            Placeholder::ML_DOMAIN,
            Placeholder::ML_IP,
            Placeholder::ML_IP_VALIDATED_DOMAIN,
            Placeholder::ML_IP_TYPE,
            Placeholder::ML_HELO_DOMAIN,
        ]);
        if ($flags & static::FLAG_EXP) {
            $list .= implode('', [
                Placeholder::ML_SMTP_CLIENT_IP,
                Placeholder::ML_CHECKER_DOMAIN,
                Placeholder::ML_CURRENT_TIMESTAMP,
            ]);
        }

        return $list;
    }

    public function getAllowedDelimiters(int $flags): string
    {
        return '.-+,/_=';
    }
}
