<?php

declare(strict_types=1);

namespace SPFLib\Check\State;

use SPFLib\Check\State;

/**
 * Class that holds the state of the "MAIL FROM" check process.
 */
class MailFromState extends State
{
    /**
     * {@inheritdoc}
     *
     * @see \SPFLib\Check\State::getSender()
     */
    public function getSender(): string
    {
        $mailFrom = $this->getEnvironment()->getMailFrom();
        if (($mailFrom[0] ?? '') === '@') {
            $mailFrom = 'postmaster' . $mailFrom;
        }

        return $mailFrom;
    }
}
