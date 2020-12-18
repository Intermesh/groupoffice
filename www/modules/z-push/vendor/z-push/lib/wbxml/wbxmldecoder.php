<?php
/***********************************************
* File      :   wbxmldecoder.php
* Project   :   Z-Push
* Descr     :   WBXMLDecoder decodes from Wap Binary XML
*
* Created   :   01.10.2007
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

class WBXMLDecoder extends WBXMLDefs {
    private $in;

    private $inLog;

    private $tagcp = 0;
    private $ungetbuffer;
    private $log = false;
    private $logStack = array();
    private $inputBuffer = "";
    private $isWBXML = true;

    static private $loopCounter = array();
    const MAXLOOP = 5000;

    const VERSION = 0x03;

    /**
     * Counts the amount of times a code part has been executed.
     * When being executed too often, the code throws a WBMXLException.
     *
     * @access public
     * @param String $name
     * @throws WBXMLException
     * @return boolean
     */
    static public function InWhile($name) {
        if (!isset(self::$loopCounter[$name])) {
            self::$loopCounter[$name] = 0;
        }
        else {
            self::$loopCounter[$name]++;
        }

        if (self::$loopCounter[$name] > self::MAXLOOP) {
            throw new WBXMLException(sprintf("Loop count in while too high, code '%s' exceeded max. amount of permitted loops", $name));
        }
        return true;
    }

    /**
     * Resets the inWhile counter.
     *
     * @param String $name
     * @return boolean
     */
    static public function ResetInWhile($name) {
        if (isset(self::$loopCounter[$name])) {
            unset(self::$loopCounter[$name]);
        }
        return true;
    }

    /**
     * WBXML Decode Constructor
     * We only handle ActiveSync WBXML, which is a subset of WBXML
     *
     * @param  stream      $input          the incoming data stream
     *
     * @access public
     */
    public function __construct($input) {
        $this->log = ZLog::IsWbxmlDebugEnabled();

        $this->in = $input;

        $version = $this->getByte();
        if($version != self::VERSION) {
            $this->inputBuffer .= chr($version);
            $this->isWBXML = false;
            return;
        }

        $publicid = $this->getMBUInt();
        if($publicid !== 1)
            throw new WBXMLException("Wrong publicid : ".$publicid);

        $charsetid = $this->getMBUInt();
        if ($charsetid !== 106)
            throw new WBXMLException("Wrong charset : ".$charsetid);

        $stringtablesize = $this->getMBUInt();
        if ($stringtablesize !== 0)
            throw new WBXMLException("Wrong string table size : ".$stringtablesize);
    }

    /**
     * Returns either start, content or end, and auto-concatenates successive content
     *
     * @access public
     * @return element/value
     */
    public function getElement() {
        $element = $this->getToken();

        switch($element[EN_TYPE]) {
            case EN_TYPE_STARTTAG:
                return $element;
            case EN_TYPE_ENDTAG:
                return $element;
            case EN_TYPE_CONTENT:
                WBXMLDecoder::ResetInWhile("decoderGetElement");
                while(WBXMLDecoder::InWhile("decoderGetElement")) {
                    $next = $this->getToken();
                    if($next == false)
                        return false;
                    else if($next[EN_TYPE] == EN_CONTENT) {
                        $element[EN_CONTENT] .= $next[EN_CONTENT];
                    } else {
                        $this->ungetElement($next);
                        break;
                    }
                }
                return $element;
        }

        return false;
    }

    /**
     * Get a peek at the next element
     *
     * @access public
     * @return element
     */
    public function peek() {
        $element = $this->getElement();
        $this->ungetElement($element);
        return $element;
    }

    /**
     * Get the element of a StartTag
     *
     * @param $tag
     *
     * @access public
     * @return element/boolean      returns false if not available
     */
    public function getElementStartTag($tag) {
        $element = $this->getToken();

        if (!$element)
            return false;

        if($element[EN_TYPE] == EN_TYPE_STARTTAG && $element[EN_TAG] == $tag)
            return $element;
        else {
            ZLog::Write(LOGLEVEL_WBXMLSTACK, sprintf("WBXMLDecoder->getElementStartTag(): unmatched WBXML tag: '%s' matching '%s' type '%s' flags '%s'", $tag, ((isset($element[EN_TAG]))?$element[EN_TAG]:""), ((isset($element[EN_TYPE]))?$element[EN_TYPE]:""), ((isset($element[EN_FLAGS]))?$element[EN_FLAGS]:"")));
            $this->ungetElement($element);
        }

        return false;
    }

    /**
     * Get the element of a EndTag
     *
     * @access public
     * @return element/boolean      returns false if not available
     */
    public function getElementEndTag() {
        $element = $this->getToken();

        if($element[EN_TYPE] == EN_TYPE_ENDTAG)
            return $element;
        else {
            ZLog::Write(LOGLEVEL_WBXMLSTACK, sprintf("WBXMLDecoder->getElementEndTag(): unmatched WBXML tag: '%s' type '%s' flags '%s'", ((isset($element[EN_TAG]))?$element[EN_TAG]:""), ((isset($element[EN_TYPE]))?$element[EN_TYPE]:""), ((isset($element[EN_FLAGS]))?$element[EN_FLAGS]:"")));

            $bt = debug_backtrace();
            ZLog::Write(LOGLEVEL_ERROR, sprintf("WBXMLDecoder->getElementEndTag(): could not read end tag in '%s'. Please enable the LOGLEVEL_WBXML and send the log to the Z-Push dev team.", $bt[0]["file"] . ":" . $bt[0]["line"]));

            // log the remaining wbxml content
            $this->ungetElement($element);
            while($el = $this->getElement());
        }

        return false;
    }

    /**
     * Get the content of an element
     *
     * @access public
     * @return string/boolean       returns false if not available
     */
    public function getElementContent() {
        $element = $this->getToken();

        if($element[EN_TYPE] == EN_TYPE_CONTENT) {
            return $element[EN_CONTENT];
        }
        else {
            ZLog::Write(LOGLEVEL_WBXMLSTACK, sprintf("WBXMLDecoder->getElementContent(): unmatched WBXML content: '%s' type '%s' flags '%s'", ((isset($element[EN_TAG]))?$element[EN_TAG]:""), ((isset($element[EN_TYPE]))?$element[EN_TYPE]:""), ((isset($element[EN_FLAGS]))?$element[EN_FLAGS]:"")));
            $this->ungetElement($element);
        }

        return false;
    }

    /**
     * 'Ungets' an element writing it into a buffer to be 'get' again
     *
     * @param element       $element        the element to get ungetten
     *
     * @access public
     * @return
     */
    public function ungetElement($element) {
        if($this->ungetbuffer)
            ZLog::Write(LOGLEVEL_ERROR,sprintf("WBXMLDecoder->ungetElement(): WBXML double unget on tag: '%s' type '%s' flags '%s'", ((isset($element[EN_TAG]))?$element[EN_TAG]:""), ((isset($element[EN_TYPE]))?$element[EN_TYPE]:""), ((isset($element[EN_FLAGS]))?$element[EN_FLAGS]:"")));

        $this->ungetbuffer = $element;
    }

    /**
     * Returns the plain input stream
     *
     * @access public
     * @return string
     */
    public function GetPlainInputStream() {
        return $this->inputBuffer.stream_get_contents($this->in);
    }

    /**
     * Returns if the input is WBXML
     *
     * @access public
     * @return boolean
     */
    public function IsWBXML() {
        return $this->isWBXML;
    }

    /**
     * Reads the remaning data from the input stream
     *
     * @access public
     * @return void
     */
    public function readRemainingData() {
        ZLog::Write(LOGLEVEL_DEBUG, "WBXMLDecoder->readRemainingData() reading remaining data from input stream");
        while($this->getElement());
    }

    /**----------------------------------------------------------------------------------------------------------
     * Private WBXMLDecoder stuff
     */

    /**
     * Returns the next token
     *
     * @access private
     * @return token
     */
    private function getToken() {
        // See if there's something in the ungetBuffer
        if($this->ungetbuffer) {
            $element = $this->ungetbuffer;
            $this->ungetbuffer = false;
            return $element;
        }

        $el = $this->_getToken();
        if($this->log && $el)
            $this->logToken($el);

        return $el;
    }

    /**
     * Log the a token to ZLog
     *
     * @param string    $el         token
     *
     * @access private
     * @return
     */
    private function logToken($el) {
        $spaces = str_repeat(" ", count($this->logStack));

        switch($el[EN_TYPE]) {
            case EN_TYPE_STARTTAG:
                if($el[EN_FLAGS] & EN_FLAGS_CONTENT) {
                    ZLog::Write(LOGLEVEL_WBXML,"I " . $spaces . " <". $el[EN_TAG] . ">");
                    array_push($this->logStack, $el[EN_TAG]);
                } else
                    ZLog::Write(LOGLEVEL_WBXML,"I " . $spaces . " <" . $el[EN_TAG] . "/>");

                break;
            case EN_TYPE_ENDTAG:
                $tag = array_pop($this->logStack);
                ZLog::Write(LOGLEVEL_WBXML,"I " . $spaces . "</" . $tag . ">");
                break;
            case EN_TYPE_CONTENT:
                // as we concatenate the string here, the entire content is copied.
                // when sending an email with an attachment this single log line (which is never logged in INFO)
                // requires easily additional 20 MB of RAM. See https://jira.z-hub.io/browse/ZP-1159
                $messagesize = strlen($el[EN_CONTENT]);
                if ($messagesize > 10240 && !defined('WBXML_DEBUGGING')) {
                    $content = substr($el[EN_CONTENT], 0, 10240) . sprintf(" <log message with %d bytes truncated>", $messagesize);
                }
                else {
                    $content = $el[EN_CONTENT];
                }
                // Log but make sure it's not truncated again (will be slightly bigger than 10KB)
                ZLog::Write(LOGLEVEL_WBXML,"I " . $spaces . " " . $content, false);
                break;
        }
    }

    /**
     * Returns either a start tag, content or end tag
     *
     * @access private
     * @return
     */
    private function _getToken() {
        // Get the data from the input stream
        $element = array();

        WBXMLDecoder::ResetInWhile("decoderGetToken");
        while(WBXMLDecoder::InWhile("decoderGetToken")) {
            $byte = fread($this->in, 1);
            if($byte === "" || $byte === false)
                break;
            $byte = ord($byte);

            switch($byte) {
                case self::WBXML_SWITCH_PAGE:
                    $this->tagcp = $this->getByte();
                    break;

                case self::WBXML_END:
                    $element[EN_TYPE] = EN_TYPE_ENDTAG;
                    return $element;

                case self::WBXML_STR_I:
                    $element[EN_TYPE] = EN_TYPE_CONTENT;
                    $element[EN_CONTENT] = $this->getTermStr();
                    return $element;

                case self::WBXML_OPAQUE:
                    $length = $this->getMBUInt();
                    $element[EN_TYPE] = EN_TYPE_CONTENT;
                    $element[EN_CONTENT] = $this->getOpaque($length);
                    return $element;

                case self::WBXML_ENTITY:
                case self::WBXML_LITERAL:
                case self::WBXML_EXT_I_0:
                case self::WBXML_EXT_I_1:
                case self::WBXML_EXT_I_2:
                case self::WBXML_PI:
                case self::WBXML_LITERAL_C:
                case self::WBXML_EXT_T_0:
                case self::WBXML_EXT_T_1:
                case self::WBXML_EXT_T_2:
                case self::WBXML_STR_T:
                case self::WBXML_LITERAL_A:
                case self::WBXML_EXT_0:
                case self::WBXML_EXT_1:
                case self::WBXML_EXT_2:
                case self::WBXML_LITERAL_AC:
                    throw new WBXMLException("Invalid token :".$byte);

                default:
                    if($byte & self::WBXML_WITH_ATTRIBUTES)
                        throw new WBXMLException("Attributes are not allowed :".$byte);
                    $element[EN_TYPE] = EN_TYPE_STARTTAG;
                    $element[EN_TAG] = $this->getMapping($this->tagcp, $byte & 0x3f);
                    $element[EN_FLAGS] = ($byte & self::WBXML_WITH_CONTENT ? EN_FLAGS_CONTENT : 0);
                    return $element;
            }
        }
    }

    /**
     * Reads from the stream until getting a string terminator
     *
     * @access private
     * @return string
     */
    private function getTermStr() {
        if (defined('WBXML_DEBUGGING') && WBXML_DEBUGGING === true) {
            $str = "";
            while (1) {
                $in = $this->getByte();
                if ($in == 0) {
                    break;
                }
                else {
                    $str .= chr($in);
                }
            }

            return $str;
        }

        // there is no unlimited "length" for stream_get_line,
        // so we use a huge value for "length" param (1Gb)
        // (0 == PHP_SOCK_CHUNK_SIZE (8192))
        // internaly php read at most PHP_SOCK_CHUNK_SIZE at a time,
        // so we can use a huge value for "length" without problem
        return stream_get_line($this->in, 1073741824, "\0");
    }

    /**
     * Reads $len from the input stream
     *
     * @param int   $len
     *
     * @access private
     * @return string
     */
    private function getOpaque($len) {
        $d = stream_get_contents($this->in, $len);
        if ($d === false)
            throw new HTTPReturnCodeException("WBXMLDecoder->getOpaque(): stream_get_contents === false", HTTP_CODE_500, null, LOGLEVEL_WARN);
        $l = strlen($d);
        if ($l !== $len)
            throw new HTTPReturnCodeException("WBXMLDecoder->getOpaque(): only $l byte read instead of $len", HTTP_CODE_500, null, LOGLEVEL_WARN);
        return $d;
    }

    /**
     * Reads one byte from the input stream
     *
     * @access private
     * @return int
     */
    private function getByte() {
        $ch = fread($this->in, 1);
        if(strlen($ch) > 0)
            return ord($ch);
        else
            return;
    }

    /**
     * Reads string length from the input stream
     *
     * @access private
     * @return
     */
    private function getMBUInt() {
        $uint = 0;

        while(1) {
          $byte = $this->getByte();

          $uint |= $byte & 0x7f;

          if($byte & 0x80)
              $uint = $uint << 7;
          else
              break;
        }

        return $uint;
    }

    /**
     * Returns the mapping for a specified codepage and id
     *
     * @param $cp   codepage
     * @param $id
     *
     * @access public
     * @return string
     */
    private function getMapping($cp, $id) {
        if(!isset($this->dtd["codes"][$cp]) || !isset($this->dtd["codes"][$cp][$id]))
            return false;
        else {
            if(isset($this->dtd["namespaces"][$cp])) {
                return $this->dtd["namespaces"][$cp] . ":" . $this->dtd["codes"][$cp][$id];
            } else
                return $this->dtd["codes"][$cp][$id];
        }
    }
}
