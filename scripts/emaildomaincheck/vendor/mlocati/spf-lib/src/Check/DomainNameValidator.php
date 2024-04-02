<?php

declare(strict_types=1);

namespace SPFLib\Check;

use SPFLib\Exception\InvalidDomainException;
use SPFLib\Macro\MacroString;

/**
 * Class that checks if a domain name is valid.
 */
class DomainNameValidator
{
    /**
     * @var int
     */
    protected const MIN_NUM_LABELS = 2;

    /**
     * @var int
     */
    protected const MAX_LABEL_SIZE = 63;

    /**
     * @var int
     */
    protected const MAX_TOTAL_LENGTH = 253;

    /**
     * Check if a domain name is valid.
     *
     * @throws \SPFLib\Exception\InvalidDomainException
     */
    public function check(string $domain, ?MacroString $derivedFrom = null): string
    {
        // Let's ignore 1 trailing dot
        $domain = preg_replace('/^(.+[^\.])\.$/', '\1', $domain);

        $error = '';
        $error = $error ?: $this->checkNotEmpty($domain);
        $error = $error ?: $this->checkNoSpaces($domain);
        $error = $error ?: $this->checkMinLabelsCount($domain);
        $error = $error ?: $this->checkMaxLabelSize($domain);
        $error = $error ?: $this->checkInvalidChars($domain);
        $error = $error ?: $this->checkTopLabel($domain);
        if ($error !== '') {
            throw new InvalidDomainException($domain, $error, $derivedFrom);
        }

        return $this->ensureMaxTotalLength($domain);
    }

    protected function checkNotEmpty(string $domain): string
    {
        if (trim($domain, ' .') === '') {
            return 'the domain name is empty';
        }

        return '';
    }

    protected function checkNoSpaces(string $domain): string
    {
        if (trim($domain) !== $domain) {
            return 'the domain starts or ends with a space';
        }

        return '';
    }

    /**
     * @throws \SPFLib\Exception\InvalidDomainException
     */
    protected function checkMinLabelsCount(string $domain): string
    {
        $labels = explode('.', trim($domain, '.'));
        $numLabels = count($labels);
        if ($numLabels < static::MIN_NUM_LABELS) {
            return "the domain has {$numLabels} labels (less than " . static::MIN_NUM_LABELS . ')';
        }

        return '';
    }

    protected function checkMaxLabelSize(string $domain): string
    {
        $labels = explode('.', $domain);
        foreach ($labels as $label) {
            $labelLength = strlen($label);
            if ($labelLength > static::MAX_LABEL_SIZE) {
                return 'the domain contains a label longer than ' . static::MAX_LABEL_SIZE . ' octects';
            }
        }

        return '';
    }

    protected function checkInvalidChars(string $domain): string
    {
        return '';
        $matches = 0;
        if (!preg_match_all('/[%]/', $domain, $matches)) {
            return '';
        }
        $invalidChars = array_values(array_unique($matches[0]));

        return isset($invalidChars[1]) ? 'the domain contains these invalid characters: "' . implode("', '", $invalidChars) . '"' : "the domain contains this invalid character: \"{$invalidChars[0]}\"";
    }

    protected function checkTopLabel(string $domain): string
    {
        $alternatives = [
            '[a-z0-9]*[a-z][a-z0-9]*',
            '[a-z0-9]+-[a-z0-9\-]*[a-z0-9]',
        ];
        $rx = '/(^|\.)((' . implode(')|(', $alternatives) . '))$/i';
        if (preg_match($rx, $domain)) {
            return '';
        }

        return 'the top label is not valid';
    }

    protected function ensureMaxTotalLength(string $domain): string
    {
        while (strlen($domain) > static::MAX_TOTAL_LENGTH) {
            $domain = preg_replace('/^[^\.]*\.(.+)$/', '\1', $domain);
        }

        return $domain;
    }
}
