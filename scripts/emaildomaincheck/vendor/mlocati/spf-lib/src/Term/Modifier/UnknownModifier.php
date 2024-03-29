<?php

declare(strict_types=1);

namespace SPFLib\Term\Modifier;

use SPFLib\Macro\MacroString;
use SPFLib\Term\Modifier;

/**
 * Class that represents the "unknown" modifier.
 *
 * @see https://tools.ietf.org/html/rfc7208#section-12
 */
class UnknownModifier extends Modifier
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \SPFLib\Macro\MacroString
     */
    private $value;

    /**
     * Initialize the instance.
     *
     * @param \SPFLib\Macro\MacroString|string|null $value
     *
     * @throws \SPFLib\Exception\InvalidMacroStringException if $value is a non empty string which does not represent a valid MacroString
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        if (!$value instanceof MacroString) {
            $value = MacroString\Decoder::getInstance()->decode($value === null ? '' : $value, MacroString\Decoder::FLAG_ALLOWEMPTY);
        }
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term::__toString()
     */
    public function __toString(): string
    {
        return $this->getName() . '=' . (string) $this->getValue();
    }

    public function __clone()
    {
        $this->value = clone $this->getValue();
    }

    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Term\Modifier::getName()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the name of the modifier (the part after '=').
     */
    public function getValue(): MacroString
    {
        return $this->value;
    }
}
