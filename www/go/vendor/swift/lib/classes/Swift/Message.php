<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The Message class for building emails.
 *
 * @package    Swift
 * @subpackage Mime
 * @author     Chris Corbyn
 */
class Swift_Message extends Swift_Mime_SimpleMessage
{
    /**
     * Create a new Message.
     *
     * Details may be optionally passed into the constructor.
     *
     * @param StringHelper $subject
     * @param StringHelper $body
     * @param StringHelper $contentType
     * @param StringHelper $charset
     */
    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {
        call_user_func_array(
            array($this, 'Swift_Mime_SimpleMessage::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('mime.message')
            );

        if (!isset($charset)) {
            $charset = Swift_DependencyContainer::getInstance()
                ->lookup('properties.charset');
        }
        $this->setSubject($subject);
        $this->setBody($body);
        $this->setCharset($charset);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    /**
     * Create a new Message.
     *
     * @param StringHelper $subject
     * @param StringHelper $body
     * @param StringHelper $contentType
     * @param StringHelper $charset
     *
     * @return Swift_Message
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new self($subject, $body, $contentType, $charset);
    }

    /**
     * Add a MimePart to this Message.
     *
     * @param StringHelper|Swift_OutputByteStream $body
     * @param StringHelper                        $contentType
     * @param StringHelper                        $charset
     *
     * @return Swift_Mime_SimpleMessage
     */
    public function addPart($body, $contentType = null, $charset = null)
    {
        return $this->attach(Swift_MimePart::newInstance(
            $body, $contentType, $charset
            ));
    }

    public function __wakeup()
    {
        Swift_DependencyContainer::getInstance()->createDependenciesFor('mime.message');
    }
}
