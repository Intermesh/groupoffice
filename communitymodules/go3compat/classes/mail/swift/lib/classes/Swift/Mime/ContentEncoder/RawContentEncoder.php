<?php

/*
 A plain text transfer encoder in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/Mime/ContentEncoder.php';
//@require 'Swift/InputByteStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * Handles binary/7/8-bit Transfer Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_ContentEncoder_RawContentEncoder
  implements Swift_Mime_ContentEncoder
{
  
  /**
   * The name of this encoding scheme (probably 7bit or 8bit).
   * @var StringHelper
   * @access private
   */
  private $_name;
  
  /**
   * True if canonical transformations should be done.
   * @var boolean
   * @access private
   */
  private $_canonical;
  
  /**
   * Creates a new PlainContentEncoder with $name (probably 7bit or 8bit).
   * @param StringHelper $name
   * @param boolean $canonical If canonicalization transformation should be done.
   */
  public function __construct($name, $canonical = false)
  {
    $this->_name = $name;
    $this->_canonical = $canonical;
  }
  
  /**
   * Encode a given string to produce an encoded string.
   * @param StringHelper $string
   * @param int $firstLineOffset, ignored
   * @param int $maxLineLength - 0 means no wrapping will occur
   * @return StringHelper
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    if ($this->_canonical)
    {
      $string = $this->_canonicalize($string);
    }
    return $string;
  }
  
  /**
   * Encode stream $in to stream $out.
   * @param Swift_OutputByteStream $in
   * @param Swift_InputByteStream $out
   * @param int $firstLineOffset, ignored
   * @param int $maxLineLength, optional, 0 means no wrapping will occur
   */
  public function encodeByteStream(
    Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    while (false !== $bytes = $os->read(8192))
    {      
      $is->write($bytes);
    }
  }
  
  /**
   * Get the name of this encoding scheme.
   * @return StringHelper
   */
  public function getName()
  {
    return $this->_name;
  }
  
  /**
   * Not used.
   */
  public function charsetChanged($charset)
  {
  }
  
  /**
   * Canonicalize string input (fix CRLF).
   * @param StringHelper $string
   * @return StringHelper
   * @access private
   */
  private function _canonicalize($string)
  {
    return str_replace(
      array("\r\n", "\r", "\n"),
      array("\n", "\n", "\r\n"),
      $string
      );
  }
  
}
