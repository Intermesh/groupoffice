<?php
/**
 * The MimeDecode class is used to decode mail/mime messages
 *
 * This class will parse a raw mime email and return
 * the structure. Returned structure is similar to
 * that returned by imap_fetchstructure().
 *
 *  +----------------------------- IMPORTANT ------------------------------+
 *  | Usage of this class compared to native php extensions such as        |
 *  | mailparse or imap, is slow and may be feature deficient. If available|
 *  | you are STRONGLY recommended to use the php extensions.              |
 *  +----------------------------------------------------------------------+
 *
 * Compatible with PHP versions 4 and 5
 *
 * LICENSE: This LICENSE is in the BSD license style.
 * Copyright (c) 2002-2003, Richard Heyes <richard@phpguru.org>
 * Copyright (c) 2003-2006, PEAR <pear-group@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or
 * without modification, are permitted provided that the following
 * conditions are met:
 *
 * - Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * - Neither the name of the authors, nor the names of its contributors
 *   may be used to endorse or promote products derived from this
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Mail
 * @package    Mail_Mime
 * @author     Richard Heyes  <richard@phpguru.org>
 * @author     George Schlossnagle <george@omniti.com>
 * @author     Cipriano Groenendal <cipri@php.net>
 * @author     Sean Coates <sean@php.net>
 * @copyright  2003-2006 PEAR <pear-group@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version    CVS: $Id: mimeDecode.php 305875 2010-12-01 07:17:10Z alan_k $
 * @link       http://pear.php.net/package/Mail_mime
 */


/**
 * require PEAR
 *
 * This package depends on PEAR to raise errors.
 */
//require_once 'PEAR.php';


/**
 * The MimeDecode class is used to decode mail/mime messages
 *
 * This class will parse a raw mime email and return the structure.
 * Returned structure is similar to that returned by imap_fetchstructure().
 *
 *  +----------------------------- IMPORTANT ------------------------------+
 *  | Usage of this class compared to native php extensions such as        |
 *  | mailparse or imap, is slow and may be feature deficient. If available|
 *  | you are STRONGLY recommended to use the php extensions.              |
 *  +----------------------------------------------------------------------+
 *
 * @category   Mail
 * @package    Mail_Mime
 * @author     Richard Heyes  <richard@phpguru.org>
 * @author     George Schlossnagle <george@omniti.com>
 * @author     Cipriano Groenendal <cipri@php.net>
 * @author     Sean Coates <sean@php.net>
 * @copyright  2003-2006 PEAR <pear-group@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Mail_mime
 */

namespace GO\Base\Mail;


use Exception;
use stdClass;

class MimeDecode
{
	/**
	 * The raw email to decode
	 *
	 * @var string
	 * @access private
	 */
	private $input;

	/**
	 * The header part of the input
	 *
	 * @var string
	 * @access private
	 */
	private $header;

	/**
	 * The body part of the input
	 *
	 * @var string
	 * @access private
	 */
	private $body;

	/**
	 * If an error occurs, this is used to store the message
	 *
	 * @var string
	 * @access private
	 */
	private $error;

	/**
	 * Flag to determine whether to include bodies in the
	 * returned object.
	 *
	 * @var    boolean
	 * @access private
	 */
	private $include_bodies;

	/**
	 * Flag to determine whether to decode bodies
	 *
	 * @var    boolean
	 * @access private
	 */
	private $decode_bodies;

	/**
	 * Flag to determine whether to decode headers
	 *
	 * @var    boolean
	 * @access private
	 */
	private $decode_headers;

	/**
	 * Flag to determine whether to include attached messages
	 * as body in the returned object. Depends on $include_bodies
	 *
	 * @var    boolean
	 * @access private
	 */
	private $rfc822_bodies;
	/**
	 * @var \go\core\mail\MimeDecode
	 */
	private $coreDecode;

	/**
	 * Constructor.
	 *
	 * Sets up the object, initialise the variables, and splits and
	 * stores the header and body of the input.
	 *
	 * @param string $input The input to decode
	 * @access public
	 */
	public function __construct(string $input)
	{

		$this->coreDecode = new \go\core\mail\MimeDecode($input);
	}

	/**
	 * Begins the decoding process. If called statically
	 * it will create an object and call the decode() method
	 * of it.
	 *
	 * @param array An array of various parameters that determine
	 *              various things:
	 *              include_bodies - Whether to include the body in the returned
	 *                               object.
	 *              decode_bodies  - Whether to decode the bodies
	 *                               of the parts. (Transfer encoding)
	 *              decode_headers - Whether to decode headers
	 *              input          - If called statically, this will be treated
	 *                               as the input
	 * @return stdClass Decoded results
	 * @access public
	 */
	public function decode(array $params = null) : stdClass
	{

		$this->coreDecode->includeBodies = $params['include_bodies'] ?? false;
		$this->coreDecode->decodeBodies = $params['decode_bodies'] ?? false;
		$this->coreDecode->decodeHeaders = $params['decode_headers'] ?? false;


		return $this->coreDecode->decodeToArray();
	}
} 
