<?php

namespace MLocati\IDNA;

use MLocati\IDNA\CodepointConverter\CodepointConverterInterface;
use MLocati\IDNA\CodepointConverter\Utf8;
use MLocati\IDNA\Exception\InvalidDomainNameCharacters;

class DomainName
{
    /**
     * The domain name with normalized (ie valid) characters.
     *
     * @var string
     */
    protected $name;

    /**
     * The punycode of the normalized name.
     *
     * @var string
     */
    protected $punycode;

    /**
     * The domain name deviated from IDNA2003 to IDNA2008 (non empty only if different).
     *
     * @var string
     */
    protected $deviatedName;

    /**
     * The punycode of the deviated name.
     *
     * @var string
     */
    protected $deviatedPunycode;

    /**
     * The converter to be used to convert characters to/from Unicode code points.
     *
     * @var \MLocati\IDNA\CodepointConverter\CodepointConverterInterface
     */
    protected $codepointConverter;

    /**
     * Initializes the instance.
     *
     * @param int[] $codepoints
     * @param \MLocati\IDNA\CodepointConverter\CodepointConverterInterface|null $codepointConverter
     */
    protected function __construct(array $codepoints, CodepointConverterInterface $codepointConverter = null)
    {
        $this->codepointConverter = ($codepointConverter === null) ? static::getDefaultCodepointConverter() : $codepointConverter;

        $codepoints = $this->removeIgnored($codepoints);
        $codepoints = $this->applyMapping($codepoints);
        $this->checkValid($codepoints);
        $this->name = $this->codepointConverter->codepointsToString($codepoints);
        $this->punycode = Punycode::encodeDomainName($codepoints);
        $deviatedCodepoints = $this->applyDeviations($codepoints);
        if ($deviatedCodepoints === null) {
            $this->deviatedName = '';
            $this->deviatedPunycode = '';
        } else {
            $this->deviatedName = $this->codepointConverter->codepointsToString($deviatedCodepoints);
            $this->deviatedPunycode = Punycode::encodeDomainName($deviatedCodepoints);
        }

        return $this;
    }

    /**
     * Creates a new instance of the class starting from a string containing the domain name.
     *
     * @param string $name The domain name
     * @param \MLocati\IDNA\CodepointConverter\CodepointConverterInterface|null $codepointConverter The converter to handle the name (defaults to UTF-8)
     *
     * @throws \MLocati\IDNA\Exception\InvalidString Throws an InvalidString exception if $name contains characters outside the encoding handled by $codepointConverter
     * @throws \MLocati\IDNA\Exception\InvalidDomainNameCharacters Throws an InvalidDomainNameCharacters if $name contains characters marked as Invalid by the IDNA Mapping table
     *
     * @return static
     */
    public static function fromName($name, CodepointConverterInterface $codepointConverter = null)
    {
        if ($codepointConverter === null) {
            $codepointConverter = static::getDefaultCodepointConverter();
        }

        return new static($codepointConverter->stringToCodepoints($name), $codepointConverter);
    }

    /**
     * Creates a new instance of the class starting from a string containing the domain punycode.
     *
     * @param string $punycode The punycode
     * @param \MLocati\IDNA\CodepointConverter\CodepointConverterInterface|null $codepointConverter The converter to handle the name (defaults to UTF-8)
     *
     * @throws \MLocati\IDNA\Exception\InvalidPunycode Throws an InvalidPunycode exception if $punycode is not a valid
     * @throws \MLocati\IDNA\Exception\InvalidDomainNameCharacters Throws an InvalidDomainNameCharacters if the domain name corresponding th punycode contains characters marked as Invalid by the IDNA Mapping table
     *
     * @return static
     */
    public static function fromPunycode($punycode, CodepointConverterInterface $codepointConverter = null)
    {
        return new static(Punycode::decodeDomainName($punycode), $codepointConverter);
    }

    /**
     * Get the domain name with normalized (ie valid) characters.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the punycode (of the normalized name).
     *
     * @return string
     */
    public function getPunycode()
    {
        return $this->punycode;
    }

    /**
     * Check if the domain name deviated from IDNA2003 to IDNA2008.
     *
     * @return bool
     */
    public function isDeviated()
    {
        return $this->deviatedName !== '';
    }

    /**
     * Get the domain name deviated from IDNA2003 to IDNA2008 (empty if it does not deviate).
     *
     * @return string
     */
    public function getDeviatedName()
    {
        return $this->deviatedName;
    }

    /**
     * Get the the punycode of the deviated name.
     *
     * @return string
     */
    public function getDeviatedPunycode()
    {
        return $this->deviatedPunycode;
    }

    /**
     * Remove ignored code points.
     *
     * @param int[] $codepoints
     *
     * @return int[]
     */
    protected function removeIgnored(array $codepoints)
    {
        $result = array();
        foreach ($codepoints as $codepoint) {
            if (!IdnaMap::isIgnored($codepoint)) {
                $result[] = $codepoint;
            }
        }

        return $result;
    }

    /**
     * Map code points accordingly to the IDNA Mapping table.
     *
     * @param int[] $codepoints
     *
     * @return int[]
     */
    protected function applyMapping(array $codepoints)
    {
        $result = array();
        foreach ($codepoints as $codepoint) {
            $mapped = IdnaMap::getMapped($codepoint);
            if ($mapped === null) {
                $result[] = $codepoint;
            } else {
                $result = array_merge($result, $mapped);
            }
        }

        return $result;
    }

    /**
     * Check that a list of code points does not contain values marked as invalid by the IDNA Mapping table.
     *
     * @param int[] $codepoints
     *
     * @throws \MLocati\IDNA\Exception\InvalidDomainNameCharacters
     */
    protected function checkValid(array $codepoints)
    {
        $invalidCodepoints = array();
        $invalidCharacters = array();
        foreach ($codepoints as $codepoint) {
            if (IdnaMap::getDeviation($codepoint) === null) {
                if (IdnaMap::isValid($codepoint, array(IdnaMap::EXCLUDE_ALWAYS, IdnaMap::EXCLUDE_CURRENT)) !== true) {
                    if (!in_array($codepoint, $invalidCodepoints)) {
                        $invalidCodepoints[] = $codepoint;
                        if ($invalidCharacters !== null) {
                            try {
                                $invalidCharacters[] = $this->codepointConverter->codepointToCharacter($codepoint);
                            } catch (\Exception $x) {
                                $invalidCharacters = null;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($invalidCodepoints)) {
            throw new InvalidDomainNameCharacters($invalidCodepoints, ($invalidCharacters === null) ? '' : implode("\n", $invalidCharacters));
        }
    }

    /**
     * Map the code points marked as deviated from IDNA2003 to IDNA2008.
     *
     * @param int[] $codepoints The code points with values in the IDNA2008 valid range
     *
     * @return int[]|null The code points with values in the IDNA2003 valid range. If no deviated code point is found, you'll have null back
     */
    protected function applyDeviations(array $codepoints)
    {
        $someFound = false;
        $result = array();
        foreach ($codepoints as $codepoint) {
            $deviated = IdnaMap::getDeviation($codepoint);
            if ($deviated === null) {
                $result[] = $codepoint;
            } else {
                $someFound = true;
                $result = array_merge($result, $deviated);
            }
        }

        return $someFound ? $result : null;
    }

    /**
     * Get the default CodepointConverter.
     *
     * @return \MLocati\IDNA\CodepointConverter\CodepointConverterInterface
     */
    protected static function getDefaultCodepointConverter()
    {
        return new Utf8();
    }
}
