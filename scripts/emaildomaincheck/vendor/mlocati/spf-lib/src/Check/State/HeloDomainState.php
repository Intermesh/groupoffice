<?php

declare(strict_types=1);

namespace SPFLib\Check\State;

use SPFLib\Check\State;

/**
 * Class that holds the state of the "HELO"/"EHLO" check process.
 */
class HeloDomainState extends State
{
    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Check\State::getSender()
     */
    public function getSender(): string
    {
        $domain = $this->getEnvironment()->getHeloDomain();

        return $domain === '' ? '' : "postmaster@{$domain}";
    }
}
