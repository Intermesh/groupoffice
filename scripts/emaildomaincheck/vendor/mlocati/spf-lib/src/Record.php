<?php

declare(strict_types=1);

namespace SPFLib;

/**
 * Class that holds the data of a SPF TXT record.
 */
class Record
{
    /**
     * The prefix of the SPF record.
     *
     * @var string
     */
    public const PREFIX = 'v=spf1';

    /**
     * @var \SPFLib\Term[]
     */
    private $terms = [];

    public function __toString(): string
    {
        return rtrim(static::PREFIX . ' ' . implode(' ', $this->getTerms()), ' ');
    }

    public function __clone()
    {
        $terms = $this->getTerms();
        $this->clearTerms();
        foreach ($terms as $term) {
            $this->addTerm(clone $term);
        }
    }

    /**
     * Clear all the terms.
     *
     * @return $this
     */
    public function clearTerms(): self
    {
        $this->terms = [];

        return $this;
    }

    /**
     * @return $this
     */
    public function addTerm(Term $term): self
    {
        $this->terms[] = $term;

        return $this;
    }

    /**
     * @return \SPFLib\Term[]
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    /**
     * @return \SPFLib\Term\Mechanism[]
     */
    public function getMechanisms(): array
    {
        return array_values(
            array_filter(
                $this->getTerms(),
                static function (Term $term): bool {
                    return $term instanceof Term\Mechanism;
                }
            )
        );
    }

    /**
     * @return \SPFLib\Term\Modifier[]
     */
    public function getModifiers(): array
    {
        return array_values(
            array_filter(
                $this->getTerms(),
                static function (Term $term): bool {
                    return $term instanceof Term\Modifier;
                }
            )
        );
    }
}
