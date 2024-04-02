<?php

declare(strict_types=1);

namespace SPFLib\Macro\MacroString;

use IPLib\Address;
use SPFLib\Check\State;
use SPFLib\Exception;
use SPFLib\Macro\MacroString;

/**
 * Class that expands a macro string with the values of the current check environment/execution.
 */
class Expander
{
    /**
     * Apply the environment/current execution info to a MacroString instance, getting its final value.
     *
     * @throws \SPFLib\Exception\MissingEnvironmentValueException if $state is missing a value used one of the placeholders of the MacroString
     * @throws \SPFLib\Exception\TooManyDNSLookupsException if too many DNS queries have been performed
     *
     * @return string
     */
    public function expand(MacroString $macroString, string $currentDomain, State $state): string
    {
        return implode(
            '',
            array_map(
                function (Chunk $chunk) use ($currentDomain, $state): string {
                    return $this->expandChunk($chunk, $currentDomain, $state);
                },
                $macroString->getChunks()
            )
        );
    }

    /**
     * @throws \SPFLib\Exception\MissingEnvironmentValueException if $state is missing a value used a placeholder (if $chunk is a placeholder)
     * @throws \SPFLib\Exception\TooManyDNSLookupsException if too many DNS queries have been performed
     */
    protected function expandChunk(Chunk $chunk, string $currentDomain, State $state): string
    {
        if ($chunk instanceof Chunk\LiteralString) {
            return $this->expandLiteralString($chunk, $currentDomain, $state);
        }
        if ($chunk instanceof Chunk\Placeholder) {
            return $this->expandPlaceholder($chunk, $currentDomain, $state);
        }
    }

    protected function expandLiteralString(Chunk\LiteralString $literalString, string $currentDomain, State $state): string
    {
        return strtr(
            (string) $literalString,
            [
                '%%' => '%',
                '%_' => ' ',
                '%-' => '%20',
            ]
        );
    }

    /**
     * @throws \SPFLib\Exception\MissingEnvironmentValueException if $state is missing a value used by the placeholder
     * @throws \SPFLib\Exception\TooManyDNSLookupsException if too many DNS queries have been performed
     */
    protected function expandPlaceholder(Chunk\Placeholder $placeholder, string $currentDomain, State $state): string
    {
        return $this->transformPlaceholderValue(
            $placeholder,
            $this->getPlaceholderValue($placeholder->getMacroLetter(), $currentDomain, $state)
        );
    }

    /**
     * @throws \SPFLib\Exception\MissingEnvironmentValueException if $state is missing a value used by the placeholder
     * @throws \SPFLib\Exception\TooManyDNSLookupsException if too many DNS queries have been performed
     */
    protected function getPlaceholderValue(string $macroLetter, string $currentDomain, State $state): string
    {
        $value = '';
        switch (strtolower($macroLetter)) {
            case Chunk\Placeholder::ML_SENDER:
                $value = $state->getSender();
                break;
            case Chunk\Placeholder::ML_SENDER_LOCAL_PART:
                if ($state->getSender() === '') {
                    throw new Exception\MissingEnvironmentValueException(Chunk\Placeholder::ML_SENDER);
                }
                $value = $state->getSenderLocalPart();
                break;
            case Chunk\Placeholder::ML_SENDER_DOMAIN:
                if ($state->getSender() === '') {
                    throw new Exception\MissingEnvironmentValueException(Chunk\Placeholder::ML_SENDER);
                }
                $value = $state->getSenderDomain();
                break;
            case Chunk\Placeholder::ML_DOMAIN:
                $value = $currentDomain;
                break;
            case Chunk\Placeholder::ML_IP:
                $ip = $state->getEnvironment()->getClientIP();
                if ($ip !== null) {
                    if ($ip instanceof Address\IPv6) {
                        $value = implode('.', str_split(str_replace(':', '', $ip->toString(true)), 1));
                    } else {
                        $value = (string) $ip;
                    }
                }
                break;
            case Chunk\Placeholder::ML_IP_VALIDATED_DOMAIN:
                $value = $state->getValidatedDomain($state->getSenderDomain(), true);
                if ($value === '') {
                    $value = 'unknown';
                }
                break;
            case Chunk\Placeholder::ML_IP_TYPE:
                $ip = $state->getEnvironment()->getClientIP();
                if ($ip === null) {
                    throw new Exception\MissingEnvironmentValueException(Chunk\Placeholder::ML_IP);
                }
                if ($ip instanceof Address\IPv4) {
                    $value = 'in-addr';
                } elseif ($ip instanceof Address\IPv6) {
                    $value = 'ip6';
                }
                break;
            case Chunk\Placeholder::ML_HELO_DOMAIN:
                $value = $state->getEnvironment()->getHeloDomain();
                break;
            case Chunk\Placeholder::ML_SMTP_CLIENT_IP:
                $ip = $state->getEnvironment()->getClientIP();
                if ($ip === null) {
                    throw new Exception\MissingEnvironmentValueException(Chunk\Placeholder::ML_IP);
                }
                $value = (string) $ip;
                break;
            case Chunk\Placeholder::ML_CHECKER_DOMAIN:
                $value = $state->getEnvironment()->getCheckerDomain();
                break;
            case Chunk\Placeholder::ML_CURRENT_TIMESTAMP:
                $value = (string) time();
                break;
        }
        if ($value === '') {
            throw new Exception\MissingEnvironmentValueException($macroLetter);
        }
        if ($macroLetter === strtoupper($macroLetter)) {
            $value = $this->urlEncode($value);
        }

        return $value;
    }

    protected function transformPlaceholderValue(Chunk\Placeholder $placeholder, string $value): string
    {
        $numOutputParts = $placeholder->getNumOutputParts();
        $reverse = $placeholder->isReverse();
        $delimiters = $placeholder->getDelimiters();
        if ($numOutputParts === null && $reverse === false && $delimiters === '') {
            return $value;
        }
        if ($delimiters === '') {
            $delimiters = '.';
        }
        $parts = preg_split('/[' . preg_quote($delimiters, '/') . ']/', $value);
        if ($reverse) {
            $parts = array_reverse($parts, false);
        }
        if ($numOutputParts !== null) {
            $numParts = count($parts);
            $strip = $numParts - $numOutputParts;
            if ($strip > 0) {
                $parts = array_splice($parts, $strip, $numOutputParts);
            }
        }

        return implode('.', $parts);
    }

    /**
     * @see https://tools.ietf.org/html/rfc3986#section-2.3
     */
    protected function urlEncode(string $value): string
    {
        return preg_replace_callback(
            '/[^\w\-\.~]/',
            static function (array $match): string {
                return '%' . substr('0' . strtolower(dechex(ord($match[0]))), -2);
            },
            $value
        );
    }
}
