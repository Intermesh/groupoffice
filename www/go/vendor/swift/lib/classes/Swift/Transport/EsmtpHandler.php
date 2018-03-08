<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An ESMTP handler.
 *
 * @package    Swift
 * @subpackage Transport
 * @author     Chris Corbyn
 */
interface Swift_Transport_EsmtpHandler
{
    /**
     * Get the name of the ESMTP extension this handles.
     *
     * @return boolean
     */
    public function getHandledKeyword();

    /**
     * Set the parameters which the EHLO greeting indicated.
     *
     * @param StringHelper[] $parameters
     */
    public function setKeywordParams(array $parameters);

    /**
     * Runs immediately after a EHLO has been issued.
     *
     * @param Swift_Transport_SmtpAgent $agent to read/write
     */
    public function afterEhlo(Swift_Transport_SmtpAgent $agent);

    /**
     * Get params which are appended to MAIL FROM:<>.
     *
     * @return StringHelper[]
     */
    public function getMailParams();

    /**
     * Get params which are appended to RCPT TO:<>.
     *
     * @return StringHelper[]
     */
    public function getRcptParams();

    /**
     * Runs when a command is due to be sent.
     *
     * @param Swift_Transport_SmtpAgent $agent            to read/write
     * @param StringHelper                    $command          to send
     * @param int[]                     $codes            expected in response
     * @param StringHelper[]                  $failedRecipients to collect failures
     * @param boolean                   $stop             to be set true  by-reference if the command is now sent
     */
    public function onCommand(Swift_Transport_SmtpAgent $agent, $command, $codes = array(), &$failedRecipients = null, &$stop = false);

    /**
     * Returns +1, -1 or 0 according to the rules for usort().
     *
     * This method is called to ensure extensions can be execute in an appropriate order.
     *
     * @param StringHelper $esmtpKeyword to compare with
     *
     * @return int
     */
    public function getPriorityOver($esmtpKeyword);

    /**
     * Returns an array of method names which are exposed to the Esmtp class.
     *
     * @return StringHelper[]
     */
    public function exposeMixinMethods();

    /**
     * Tells this handler to clear any buffers and reset its state.
     */
    public function resetState();
}
