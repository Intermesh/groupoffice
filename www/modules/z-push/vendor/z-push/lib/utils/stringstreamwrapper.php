<?php
/***********************************************
* File      :   stringstreamwrapper.php
* Project   :   Z-Push
* Descr     :   Wraps a string as a standard php stream
*               The used method names are predefined and can not be altered.
*
* Created   :   24.11.2011
*
* Copyright 2007 - 2013, 2015 - 2016 Zarafa Deutschland GmbH
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

class StringStreamWrapper {
    const PROTOCOL = "stringstream";

    private $stringstream;
    private $position;
    private $stringlength;
    private $truncateHtmlSafe;

    /**
     * Opens the stream
     * The string to be streamed is passed over the context
     *
     * @param string    $path           Specifies the URL that was passed to the original function
     * @param string    $mode           The mode used to open the file, as detailed for fopen()
     * @param int       $options        Holds additional flags set by the streams API
     * @param string    $opened_path    If the path is opened successfully, and STREAM_USE_PATH is set in options,
     *                                  opened_path should be set to the full path of the file/resource that was actually opened.
     *
     * @access public
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$opened_path) {
        $contextOptions = stream_context_get_options($this->context);
        if (!isset($contextOptions[self::PROTOCOL]['string']))
            return false;

        $this->position = 0;

        // this is our stream!
        $this->stringstream = $contextOptions[self::PROTOCOL]['string'];
        $this->truncateHtmlSafe = (isset($contextOptions[self::PROTOCOL]['truncatehtmlsafe'])) ? $contextOptions[self::PROTOCOL]['truncatehtmlsafe'] : false;

        $this->stringlength = strlen($this->stringstream);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("StringStreamWrapper::stream_open(): initialized stream length: %d - HTML-safe-truncate: %s", $this->stringlength,  Utils::PrintAsString($this->truncateHtmlSafe)));

        return true;
    }

    /**
     * Reads from stream
     *
     * @param int $len      amount of bytes to be read
     *
     * @access public
     * @return string
     */
    public function stream_read($len) {
        $data = substr($this->stringstream, $this->position, $len);
        $this->position += strlen($data);
        return $data;
    }

    /**
     * Writes data to the stream.
     *
     * @param string $data
     * @return int
     */
    public function stream_write($data){
        $l = strlen($data);
        $this->stringstream = substr($this->stringstream, 0, $this->position) . $data . substr($this->stringstream, $this->position += $l);
        $this->stringlength = strlen($this->stringstream);
        return $l;
    }

    /**
     * Stream "seek" functionality.
     *
     * @param int $offset
     * @param int $whence
     * @return boolean
     */
    public function stream_seek($offset, $whence = SEEK_SET) {
        if ($whence == SEEK_CUR) {
            $this->position += $offset;
        }
        else if ($whence == SEEK_END) {
            $this->position = $this->stringlength + $offset;
        }
        else {
            $this->position = $offset;
        }
        return true;
    }

    /**
     * Returns the current position on stream
     *
     * @access public
     * @return int
     */
    public function stream_tell() {
        return $this->position;
    }

   /**
     * Indicates if 'end of file' is reached
     *
     * @access public
     * @return boolean
     */
    public function stream_eof() {
        return ($this->position >= $this->stringlength);
    }

    /**
     * Truncates the stream to the new size.
     *
     * @param int $new_size
     * @return boolean
     */
    public function stream_truncate ($new_size) {
        // cut the string!
        $this->stringstream = Utils::Utf8_truncate($this->stringstream, $new_size, $this->truncateHtmlSafe);
        $this->stringlength = strlen($this->stringstream);

        if ($this->position > $this->stringlength) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("StringStreamWrapper->stream_truncate(): stream position (%d) ahead of new size of %d. Repositioning pointer to end of stream.", $this->position, $this->stringlength));
            $this->position = $this->stringlength;
        }
        return true;
    }

    /**
    * Retrieves information about a stream
    *
    * @access public
    * @return array
    */
    public function stream_stat() {
        return array(
            7               => $this->stringlength,
            'size'          => $this->stringlength,
        );
    }

   /**
     * Instantiates a StringStreamWrapper
     *
     * @param string    $string             The string to be wrapped
     * @param boolean   $truncatehtmlsafe   Indicates if a truncation should be done html-safe - default: false
     *
     * @access public
     * @return StringStreamWrapper
     */
     static public function Open($string, $truncatehtmlsafe = false) {
        $context = stream_context_create(array(self::PROTOCOL => array('string' => &$string, 'truncatehtmlsafe' => $truncatehtmlsafe)));
        return fopen(self::PROTOCOL . "://",'r', false, $context);
    }
}

stream_wrapper_register(StringStreamWrapper::PROTOCOL, "StringStreamWrapper");
