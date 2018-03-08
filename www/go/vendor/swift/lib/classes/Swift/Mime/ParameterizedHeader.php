<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A MIME Header with parameters.
 *
 * @package    Swift
 * @subpackage Mime
 * @author     Chris Corbyn
 */
interface Swift_Mime_ParameterizedHeader extends Swift_Mime_Header
{
    /**
     * Set the value of $parameter.
     *
     * @param StringHelper $parameter
     * @param StringHelper $value
     */
    public function setParameter($parameter, $value);

    /**
     * Get the value of $parameter.
     *
     * @param StringHelper $parameter
     *
     * @return StringHelper
     */
    public function getParameter($parameter);
}
