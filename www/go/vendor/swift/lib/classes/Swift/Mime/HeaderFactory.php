<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates MIME headers.
 *
 * @package    Swift
 * @subpackage Mime
 * @author     Chris Corbyn
 */
interface Swift_Mime_HeaderFactory extends Swift_Mime_CharsetObserver
{
    /**
     * Create a new Mailbox Header with a list of $addresses.
     *
     * @param StringHelper       $name
     * @param array|StringHelper $addresses
     *
     * @return Swift_Mime_Header
     */
    public function createMailboxHeader($name, $addresses = null);

    /**
     * Create a new Date header using $timestamp (UNIX time).
     *
     * @param StringHelper  $name
     * @param integer $timestamp
     *
     * @return Swift_Mime_Header
     */
    public function createDateHeader($name, $timestamp = null);

    /**
     * Create a new basic text header with $name and $value.
     *
     * @param StringHelper $name
     * @param StringHelper $value
     *
     * @return Swift_Mime_Header
     */
    public function createTextHeader($name, $value = null);

    /**
     * Create a new ParameterizedHeader with $name, $value and $params.
     *
     * @param StringHelper $name
     * @param StringHelper $value
     * @param array  $params
     *
     * @return Swift_Mime_ParameterizedHeader
     */
    public function createParameterizedHeader($name, $value = null, $params = array());

    /**
     * Create a new ID header for Message-ID or Content-ID.
     *
     * @param StringHelper       $name
     * @param StringHelper|array $ids
     *
     * @return Swift_Mime_Header
     */
    public function createIdHeader($name, $ids = null);

    /**
     * Create a new Path header with an address (path) in it.
     *
     * @param StringHelper $name
     * @param StringHelper $path
     *
     * @return Swift_Mime_Header
     */
    public function createPathHeader($name, $path = null);
}
