<?php

namespace go\core\mail;


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
 * @author      Merijn Schering <mschering@intermesh.nl>
 *
 * @copyright  2003-2006 PEAR <pear-group@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Mail_mime
 */


use Exception;
use GO;
use go\core\ErrorHandler;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use stdClass;

class MimeDecode
{

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
	public $includeBodies = true;

	/**
	 * Flag to determine whether to decode bodies
	 *
	 * @var    boolean
	 * @access private
	 */
	public $decodeBodies = true;

	/**
	 * Flag to determine whether to decode headers
	 *
	 * @var    boolean
	 * @access private
	 */
	public $decodeHeaders = true;
	/**
	 * @var array|string|string[]
	 */
	private $loadedBody;


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
		list($header, $body) = $this->splitBodyHeader($input);

		$this->header = $header;
		$this->body = $body;
	}

	/**
	 * Begins the decoding process. If called statically
	 * it will create an object and call the decode() method
	 * of it.
	 * @deprecated Use {@see decode()}
	 * @return stdClass Decoded results
	 * @throws Exception
	 */
	public function decodeToArray(): stdClass
	{
		$structure = $this->internalDecode($this->header, $this->body);
		if ($structure === false) {
			throw new Exception($this->error);
		}

		return $structure;
	}



	const SINGLE_HEADERS = ['subject', 'to', 'from', 'cc', 'bcc', 'from', 'date', 'message-id', 'content-type', 'references', 'in-reply-to'];


	/**
	 * Performs the decoding. Decodes the body string passed to it
	 * If it finds certain content-types it will call itself in a
	 * recursive fashion
	 *
	 * @param string $headers
	 * @param string $body
	 * @param string $default_ctype
	 * @return stdClass|false Results of decoding process
	 * @throws Exception
	 */
	private function internalDecode(string $headers, string $body, string $default_ctype = 'text/plain')
	{
		$return = new stdClass;
		$return->headers = array();
		$return->ctype_primary = null;
		$return->ctype_secondary = null;
		$headers = $this->parseHeaders($headers);

		foreach ($headers as $value) {
			$value['value'] = $this->decodeHeaders ? Util::mimeHeaderDecode($value['value']) : $value['value'];
			$name = strtolower($value['name']);
			if (isset($return->headers[$name])) {
				if (in_array($name, self::SINGLE_HEADERS)) {
					go()->warn("Header '" . $name . "' is allowed only once.");
					continue;
				}
				if (!is_array($return->headers[$name])) {
					$return->headers[$name] = array($return->headers[$name]);
				}
				$return->headers[$name][] = $value['value'];
			} else {
				$return->headers[$name] = $value['value'];
			}
		}


		foreach ($headers as $key => $value) {
			$headers[$key]['name'] = strtolower($value['name']);
			switch ($headers[$key]['name']) {

				case 'content-type':
					$content_type = $this->parseHeaderValue($headers[$key]['value']);

					if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
						$return->ctype_primary = $regs[1];
						$return->ctype_secondary = $regs[2];
					}

					if (isset($content_type['other'])) {
						foreach ($content_type['other'] as $p_name => $p_value) {
							$return->ctype_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-disposition':
					$content_disposition = $this->parseHeaderValue($headers[$key]['value']);
					$return->disposition = $content_disposition['value'];
					if (isset($content_disposition['other'])) {
						foreach ($content_disposition['other'] as $p_name => $p_value) {
							$return->d_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-transfer-encoding':
					$content_transfer_encoding = $this->parseHeaderValue($headers[$key]['value']);
					break;
			}
		}

		if (isset($content_type)) {
			switch (strtolower($content_type['value'])) {
				case 'text/html':
				case 'text/plain':
					$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
					$this->includeBodies ? $return->body = ($this->decodeBodies ? $this->decodeBody($body, $encoding) : $body) : null;
					break;

				case 'multipart/parallel':
				case 'multipart/appledouble': // Appledouble mail
				case 'multipart/report': // RFC1892
				case 'multipart/signed': // PGP
				case 'multipart/digest':
				case 'multipart/alternative':
				case 'multipart/related':
				case 'multipart/relative': // BUGGY ANDROID IMPLEMENTATION
				case 'multipart/mixed':
				case 'application/vnd.wap.multipart.related':
					if (!isset($content_type['other']['boundary'])) {
						$this->error = 'No boundary found for ' . $content_type['value'] . ' part';
						return false;
					}

					$default_ctype = (strtolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';

					$parts = $this->boundarySplit($body, $content_type['other']['boundary']);
					for ($i = 0; $i < count($parts); $i++) {
						list($part_header, $part_body) = $this->splitBodyHeader($parts[$i]);
						$part = $this->internalDecode($part_header, $part_body, $default_ctype);
						if ($part === false) {
							throw new Exception($this->error);
						}
						$return->parts[] = $part;
					}
					break;

//        case 'message/rfc822':
//
//						$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
//						$return->body = ($this->decodeBodies ? $this->decodeBody($body, $encoding) : $body);
//
//            $obj = new MimeDecode($body);
//            $return->parts[] = $obj->decode();
//            unset($obj);
//        break;

				default:
					if (!isset($content_transfer_encoding['value'])) $content_transfer_encoding['value'] = '7bit';
					$this->includeBodies ? $return->body = ($this->decodeBodies ? $this->decodeBody($body, $content_transfer_encoding['value']) : $body) : null;
					break;
			}

		} else {
			$ctype = explode('/', $default_ctype);
			$return->ctype_primary = $ctype[0];
			$return->ctype_secondary = $ctype[1];
			$this->includeBodies ? $return->body = ($this->decodeBodies ? $this->decodeBody($body) : $body) : null;
		}

		return $return;
	}

//	/**
//	 * Given the output of the above function, this will return an
//	 * array of references to the parts, indexed by mime number.
//	 *
//	 * @param object $structure The structure to go through
//	 * @param string $mime_number Internal use only.
//	 * @return array               Mime numbers
//	 */
//	function &getMimeNumbers(&$structure, $no_refs = false, $mime_number = '', $prepend = '')
//	{
//		$return = array();
//		if (!empty($structure->parts)) {
//			if ($mime_number != '') {
//				$structure->mime_id = $prepend . $mime_number;
//				$return[$prepend . $mime_number] = &$structure;
//			}
//			for ($i = 0; $i < count($structure->parts); $i++) {
//
//
//				if (!empty($structure->headers['content-type']) and substr(strtolower($structure->headers['content-type']), 0, 8) == 'message/') {
//					$prepend = $prepend . $mime_number . '.';
//					$mime_number = '';
//				} else {
//					$mime_number = ($mime_number == '' ? $i + 1 : sprintf('%s.%s', $mime_number, $i + 1));
//				}
//
//				$arr = &MimeDecode::getMimeNumbers($structure->parts[$i], $no_refs, $mime_number, $prepend);
//				foreach ($arr as $key => $val) {
//					$no_refs ? $return[$key] = '' : $return[$key] = &$arr[$key];
//				}
//			}
//		} else {
//			if ($mime_number == '') {
//				$mime_number = '1';
//			}
//			$structure->mime_id = $prepend . $mime_number;
//			$no_refs ? $return[$prepend . $mime_number] = '' : $return[$prepend . $mime_number] = &$structure;
//		}
//
//		return $return;
//	}

	/**
	 * Given a string containing a header and body
	 * section, this function will split them (at the first
	 * blank line) and return them.
	 *
	 * @param string Input to split apart
	 * @return array|false Contains header and body section
	 * @access private
	 */
	private function splitBodyHeader(string $input)
	{
		if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $input, $match)) {
			return array($match[1], $match[2]);
		}
		// bug #17325 - empty bodies are allowed. - we just check that at least one line
		// of headers exist.
		if (count(explode("\n", $input))) {
			return array($input, '');
		}
		$this->error = 'Could not split header and body';
		return false;
	}

	/**
	 * Parse headers given in $input and return
	 * as assoc array.
	 *
	 * @param string $input Headers to parse
	 * @return array Contains parsed headers
	 * @access private
	 */
	private function parseHeaders(string $input): array
	{
		if ($input === '') {
			return [];
		}

		$return = [];

		// Unfold the input
		$input = preg_replace("/\r?\n/", "\r\n", $input);
		//#7065 - wrapping.. with encoded stuff.. - probably not needed,
		// wrapping space should only get removed if the trailing item on previous line is a
		// encoded character
		//$input   = preg_replace("/=\r\n(\t| )+/", '=', $input);
		$input = preg_replace("/\r\n(\t| )+/", ' ', $input);

		$headers = explode("\r\n", trim($input));

		foreach ($headers as $value) {
			$hdr_name = substr($value, 0, $pos = strpos($value, ':'));
			$hdr_value = substr($value, $pos + 1);
			if (isset($hdr_value[0]) && $hdr_value[0] == ' ') $hdr_value = substr($hdr_value, 1);

			$return[] = array('name' => $hdr_name, 'value' => $hdr_value);
		}


		return $return;
	}

	/**
	 * Function to parse a header value,
	 * extract first part, and any secondary
	 * parts (after ;) This function is not as
	 * robust as it could be. Eg. header comments
	 * in the wrong place will probably break it.
	 *
	 * @param string $input Header value to parse
	 * @return array Contains parsed result
	 * @access private
	 */
	private function parseHeaderValue(string $input): array
	{
		if (($pos = strpos($input, ';')) === false) {
			$input = $this->decodeHeaders ? Util::mimeHeaderDecode($input) : $input;
			$return['value'] = trim($input);
			return $return;
		}

		$value = substr($input, 0, $pos);
		$value = $this->decodeHeaders ? Util::mimeHeaderDecode($value) : $value;
		$return['value'] = trim($value);
		$input = trim(substr($input, $pos + 1));

		if (!strlen($input) > 0) {
			return $return;
		}
		// at this point input contains xxxx=".....";zzzz="...."
		// since we are dealing with quoted strings, we need to handle this properly..
		$i = 0;
		$l = strlen($input);
		$key = '';
		$val = false; // our string - including quotes..
		$q = false; // in quote..
		$lq = ''; // last quote..

		while ($i < $l) {

			$c = $input[$i];

			$escaped = false;
			if ($c == '\\') {
				$i++;
				if ($i == $l - 1) { // end of string.
					break;
				}
				$escaped = true;
				$c = $input[$i];
			}


			// state - in key..
			if ($val === false) {
				if (!$escaped && $c == '=') {
					$val = '';
					$key = trim($key);
					$i++;
					continue;
				}
				if (!$escaped && $c == ';') {
					if ($key) { // a key without a value..
						$key = trim($key);
						$return['other'][$key] = '';
						$return['other'][strtolower($key)] = '';
					}
					$key = '';
				}
				$key .= $c;
				$i++;
				continue;
			}

			// state - in value.. (as $val is set..)

			if ($q === false) {
				// not in quote yet.
				if ((!strlen($val) || $lq !== false) && $c == ' ' || $c == "\t") {
					$i++;
					continue; // skip leading spaces after '=' or after '"'
				}
				if (!$escaped && ($c == '"' || $c == "'")) {
					// start quoted area..
					$q = $c;
					// in theory should not happen raw text in value part..
					// but we will handle it as a merged part of the string..
					$val = !strlen(trim($val)) ? '' : trim($val);
					$i++;
					continue;
				}
				// got end....
				if (!$escaped && $c == ';') {

					$val = trim($val);
					$added = false;
					if (preg_match('/\*[0-9]+$/', $key)) {
						// this is the extended aaa*0=...;aaa*1=.... code
						// it assumes the pieces arrive in order, and are valid...
						$key = preg_replace('/\*[0-9]+$/', '', $key);
						if (isset($return['other'][$key])) {
							$return['other'][$key] .= $val;
							if (strtolower($key) != $key) {
								$return['other'][strtolower($key)] .= $val;
							}
							$added = true;
						}
						// continue and use standard setters..
					}
					if (!$added) {
						$return['other'][$key] = $val;
						$return['other'][strtolower($key)] = $val;
					}
					$val = false;
					$key = '';
					$lq = false;
					$i++;
					continue;
				}

				$val .= $c;
				$i++;
				continue;
			}

			// state - in quote..
			if (!$escaped && $c == $q) {  // potential exit state..

				// end of quoted string..
				$lq = $q;
				$q = false;
				$i++;
				continue;
			}

			// normal char inside quoted string.
			$val .= $c;
			$i++;
		}

		// do we have anything left.
		if (strlen(trim($key)) || $val !== false) {

			$val = trim($val);
			$added = false;
			if ($val !== false && preg_match('/\*[0-9]+$/', $key)) {
				// no dupes due to our crazy regexp.
				$key = preg_replace('/\*[0-9]+$/', '', $key);
				if (isset($return['other'][$key])) {
					$return['other'][$key] .= $val;
					if (strtolower($key) != $key) {
						$return['other'][strtolower($key)] .= $val;
					}
					$added = true;
				}
				// continue and use standard setters..
			}
			if (!$added) {
				$return['other'][$key] = $val;
				$return['other'][strtolower($key)] = $val;
			}
		}
		// decode values.
		foreach ($return['other'] as $key => $val) {
			$return['other'][$key] = $this->decodeHeaders ? Util::mimeHeaderDecode($val) : $val;
		}
		return $return;
	}

	/**
	 * This function splits the input based
	 * on the given boundary
	 *
	 * @param string Input to parse
	 * @return array Contains array of resulting mime parts
	 * @access private
	 */
	private function boundarySplit(string $input, string $boundary): array
	{
		$parts = array();

		$bs_possible = substr($boundary, 2, -2);
		$bs_check = '\"' . $bs_possible . '\"';

		if ($boundary == $bs_check) {
			$boundary = $bs_possible;
		}
		$tmp = preg_split("/--" . preg_quote($boundary, '/') . "((?=\s)|--)/", $input);

		$len = count($tmp) - 1;
		for ($i = 1; $i < $len; $i++) {
			if (strlen(trim($tmp[$i]))) {
				$parts[] = $tmp[$i];
			}
		}

		// add the last part on if it does not end with the 'closing indicator'
		if (!empty($tmp[$len]) && strlen(trim($tmp[$len])) && $tmp[$len][0] != '-') {
			$parts[] = $tmp[$len];
		}
		return $parts;
	}


	/**
	 * Given a body string and an encoding type,
	 * this function will decode and return it.
	 *
	 * @param string $input Input body to decode
	 * @param string $encoding Encoding type to use.
	 * @return string Decoded body
	 * @access private
	 */
	private function decodeBody(string $input, string $encoding = '7bit'): string
	{
		switch (strtolower($encoding)) {
			case 'quoted-printable':
				return $this->quotedPrintableDecode($input);

			case 'base64':
				return base64_decode($input);

			default:
				return $input;
		}
	}

	/**
	 * Given a quoted-printable string, this
	 * function will decode and return it.
	 *
	 * @param string $input Input body to decode
	 * @return string Decoded body
	 * @access private
	 */
	private function quotedPrintableDecode(string $input): string
	{
		// Remove soft line breaks
		$input = preg_replace("/=\r?\n/", '', $input);

		// Replace encoded characters
		//				$input = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", $input);
		$input = preg_replace_callback('/=([a-f0-9]{2})/i', function ($matches) {
			return chr(hexdec($matches[1]));
		}, $input);

		return $input;
	}

	/**
	 * Checks the input for uuencoded files and returns
	 * an array of them. Can be called statically, eg:
	 *
	 * $files =& MimeDecode::uudecode($some_text);
	 *
	 * It will check for the begin 666 ... end syntax
	 * however and won't just blindly decode whatever you
	 * pass it.
	 *
	 * @param string $input Input body to look for attahcments in
	 * @return array  Decoded bodies, filenames and permissions
	 */
	public static function uudecode(string $input): array
	{
		$files = [];
		// Find all uuencoded sections
		preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches);

		for ($j = 0; $j < count($matches[3]); $j++) {

			$str = $matches[3][$j];
			$filename = $matches[2][$j];
			$fileperm = $matches[1][$j];

			$file = '';
			$str = preg_split("/\r?\n/", trim($str));
			$strlen = count($str);

			for ($i = 0; $i < $strlen; $i++) {
				$pos = 1;
				$d = 0;
				$len = (int)(((ord(substr($str[$i], 0, 1)) - 32) - ' ') & 077);

				while (($d + 3 <= $len) and ($pos + 4 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i], $pos, 1)) ^ 0x20);
					$c1 = (ord(substr($str[$i], $pos + 1, 1)) ^ 0x20);
					$c2 = (ord(substr($str[$i], $pos + 2, 1)) ^ 0x20);
					$c3 = (ord(substr($str[$i], $pos + 3, 1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

					$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

					$file .= chr(((($c2 - ' ') & 077) << 6) | (($c3 - ' ') & 077));

					$pos += 4;
					$d += 3;
				}

				if (($d + 2 <= $len) && ($pos + 3 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i], $pos, 1)) ^ 0x20);
					$c1 = (ord(substr($str[$i], $pos + 1, 1)) ^ 0x20);
					$c2 = (ord(substr($str[$i], $pos + 2, 1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

					$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

					$pos += 3;
					$d += 2;
				}

				if (($d + 1 <= $len) && ($pos + 2 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i], $pos, 1)) ^ 0x20);
					$c1 = (ord(substr($str[$i], $pos + 1, 1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

				}
			}
			$files[] = array('filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $file);
		}

		return $files;
	}



	private function loadMimeParts(stdClass $structure, Message $msg, string $part_number_prefix=''): void
	{
		// Apple sends contentID's that don't comply. So we replace them with new onces but we have to replace
		// this in the body too.


		$cidReplacements = [];
		if (isset($structure->parts))
		{
			//$part_number=0;
			foreach ($structure->parts as $part_number=>$part) {

				//text part and no attachment so it must be the body
				if($structure->ctype_primary=='multipart' && $structure->ctype_secondary=='alternative' &&
					$part->ctype_primary == 'text' && $part->ctype_secondary=='plain')
				{
					//check if html part is there
					if($this->hasHtmlPart($structure)){
						continue;
					}
				}


				if ($part->ctype_primary == 'text' && ($part->ctype_secondary=='plain' || $part->ctype_secondary=='html') && (!isset($part->disposition) || $part->disposition != 'attachment') && empty($part->d_parameters['filename']))
				{
					$this->convertEncoding($part);

					if (stripos($part->ctype_secondary,'plain')!==false)
					{
						$content_part = nl2br($part->body);
					}else
					{
						$content_part = $part->body;
					}
					$this->loadedBody .= $content_part;
				}elseif($part->ctype_primary=='multipart')
				{

				}elseif(isset($part->body))
				{
					//attachment
					if(!empty($part->ctype_parameters['name']))
					{
						$filename = $part->ctype_parameters['name'];
					}elseif(!empty($part->d_parameters['filename']) )
					{
						$filename = $part->d_parameters['filename'];
					}elseif(!empty($part->d_parameters['filename*']))
					{
						$filename=$part->d_parameters['filename*'];
					}else
					{
						$filename=uniqid(time());
					}

					if(!isset($part->ctype_primary)) {
						$part->ctype_primary = 'text';
					}
					if(!isset($part->ctype_secondary)) {
						$part->ctype_secondary = 'plain';
					}

					$mime_type = $this->buildContentType($part);

					//only embed if we can find the content-id in the body
					if(isset($part->headers['content-id']) && ($content_id=trim($part->headers['content-id'],' <>')) && strpos($this->loadedBody, $content_id) !== false)
					{
						$img = Attachment::fromString ($part->body, $filename, $mime_type);

						//Only set valid ID's. Iphone sends invalid content ID's sometimes.
						if (preg_match('/^.+@.+$/D',$content_id))
						{
							$img->setId($content_id);
							$msg->embed($img);
						} else{
							$msg->embed($img);
							$cidReplacements[$content_id] = $img->getId();
						}

					}else
					{
						$attachment = Attachment::fromString ($part->body, $filename,$mime_type);
						$this->attach($attachment);
					}
				}

				//$part_number++;
				if(isset($part->parts))
				{
					$this->loadMimeParts($part, $msg, $part_number_prefix.$part_number.'.');
				}

			}
		}elseif(isset($structure->body))
		{
			$this->convertEncoding($structure);
			//convert text to html
			if (stripos( $structure->ctype_secondary,'plain')!==false)
			{
				$text_part = nl2br($structure->body);
			}else
			{
				$text_part = $structure->body;
			}
			$this->loadedBody .= $text_part;
		}

		foreach($cidReplacements as $old => $new) {
			$this->loadedBody = str_replace($old, $new, $this->loadedBody);
		}
	}

	/**
	 * Try to convert the encoding of the email to UTF-8
	 *
	 * @param  stdClass $part
	 */
	private function convertEncoding(stdClass $part){
		$charset='UTF-8';

		if(isset($part->ctype_parameters['charset'])){
			$charset = strtoupper($part->ctype_parameters['charset']);
		}

		if($charset!='UTF-8'){
			$part->body = StringUtil::cleanUtf8($part->body, $charset);
			$part->body = str_ireplace($charset, 'UTF-8', $part->body);
		}
	}

	private function buildContentType(stdClass $part): string
	{
		$mime_type = $part->ctype_primary.'/'.$part->ctype_secondary;
		if(!empty($part->ctype_parameters)) {
			foreach ($part->ctype_parameters as $name => $value) {
				if ($name == 'name') {
					continue;
				}
				$mime_type .= ';' . $name . '=' . $value;
			}
		}

		return $mime_type;
	}

	private function hasHtmlPart(stdClass $structure): bool
	{
		if(isset($structure->parts)){
			foreach($structure->parts as $part){
				if($part->ctype_primary == 'text' && $part->ctype_secondary=='html')
					return true;
				else if($this->hasHtmlPart($part)){
					return true;
				}
			}
		}
		return false;
	}



	public function decode(): Message
	{
		$msg = new Message();

		$structure = $this->internalDecode($this->header, $this->body);
		if ($structure === false) {
			throw new Exception($this->error);
		}


		if (!empty($structure->headers['subject'])) {
			$msg->setSubject($structure->headers['subject']);
		}

		$to = isset($structure->headers['to']) && strpos($structure->headers['to'], 'undisclosed') === false ? $structure->headers['to'] : '';
		$cc = isset($structure->headers['cc']) && strpos($structure->headers['cc'], 'undisclosed') === false ? $structure->headers['cc'] : '';
		$bcc = isset($structure->headers['bcc']) && strpos($structure->headers['bcc'], 'undisclosed') === false ? $structure->headers['bcc'] : '';

		//workaround activesync problem where 'mailto:' is included in the mail address.
		$to = str_replace('mailto:', '', $to);
		$cc = str_replace('mailto:', '', $cc);
		$bcc = str_replace('mailto:', '', $bcc);

		try {
			$toList = new AddressList($to);
			$msg->addTo(...$toList->toArray());
		} catch (Exception $e) {
			ErrorHandler::logException($e);
		}

		try {
			$ccList = new AddressList($cc);
			$msg->addCc(...$ccList->toArray());
		} catch (Exception $e) {
			ErrorHandler::logException($e);
		}

		try {
			$bccList = new AddressList($bcc);
			$msg->addBcc(...$bccList->toArray());
		} catch (Exception $e) {
			ErrorHandler::logException($e);
		}

		if (isset($structure->headers['from'])) {

			try {
			$fromList = new AddressList(str_replace('mailto:', '', $structure->headers['from']));
			$from = $fromList[0];

				if ($from) {
						$msg->setFrom($from->getEmail(), $from->getName());
				}
			} catch (Exception $e) {
				GO::debug('Failed to add from address: ' . $e);
			}
		}

		if (isset($structure->headers['message-id'])) {
			//Microsoft had ID Message-ID: <[132345@microsoft.com]>
			$msg->setId(trim($structure->headers['message-id'], ' <>[]'));
		}

		if (isset($structure->headers['in-reply-to'])) {
			$msg->setInReplyTo(trim($structure->headers['in-reply-to'], '<>'));
		}

		if (!empty($structure->headers['references'])) {
			$refs = explode(" ", $structure->headers['references']);
			$refs = array_map(function ($ref) {
				return trim($ref, '<>');
			}, $refs);

			$msg->setReferences(...$refs);
		}

		$this->loadMimeParts($structure, $msg);


		$date = isset($structure->headers['date']) ? preg_replace('/\([^\)]*\)/', '', $structure->headers['date']) : date('c');
		try {
			$udate = new DateTime($date);
		} catch (Exception $e) {
			ErrorHandler::logException($e);
			$udate = new DateTime();
		}

		$msg->setDate($udate);

		$msg->setBody($this->loadedBody ?? "");


		return $msg;
	}

}
