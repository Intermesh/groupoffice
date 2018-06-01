<?php
/***********************************************
* File      :   mapistreamwrapper.php
* Project   :   Z-Push
* Descr     :   Wraps a mapi stream as a standard php stream
*               The used method names are predefined and can not be altered.
*
* Created   :   24.11.2011
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

class MAPIStreamWrapper {
    const PROTOCOL = "mapistream";

    private $mapistream;
    private $position;
    private $streamlength;
    private $toTruncate;
    private $truncateHtmlSafe;

    /**
     * Opens the stream
     * The mapistream reference is passed over the context
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
        if (!isset($contextOptions[self::PROTOCOL]['stream']))
            return false;

        $this->position = 0;
        $this->toTruncate = false;
        $this->truncateHtmlSafe = (isset($contextOptions[self::PROTOCOL]['truncatehtmlsafe'])) ? $contextOptions[self::PROTOCOL]['truncatehtmlsafe'] : false;

        // this is our stream!
        $this->mapistream = $contextOptions[self::PROTOCOL]['stream'];

        // get the data length from mapi
        if ($this->mapistream) {
            $stat = mapi_stream_stat($this->mapistream);
            $this->streamlength = $stat["cb"];
        }
        else {
            $this->streamlength = 0;
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("MAPIStreamWrapper::stream_open(): initialized mapistream: %s - streamlength: %d - HTML-safe-truncate: %s", $this->mapistream, $this->streamlength, Utils::PrintAsString($this->truncateHtmlSafe)));

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
        $len = ($this->position + $len > $this->streamlength) ? ($this->streamlength - $this->position) : $len;

        // read 4 additional bytes from the stream so we can always truncate correctly
        if ($this->toTruncate && $this->position+$len >= $this->streamlength) {
            $len += 4;
        }
        if ($this->mapistream) {
            $data = mapi_stream_read($this->mapistream, $len);
        }
        else {
            $data = "";
        }
        $this->position += strlen($data);

        // we need to truncate UTF8 compatible if ftruncate() was called
        if ($this->toTruncate && $this->position >= $this->streamlength) {
            $data = Utils::Utf8_truncate($data, $this->streamlength, $this->truncateHtmlSafe);
        }

        return $data;
    }

    /**
     * Stream "seek" functionality.
     *
     * @param int $offset
     * @param int $whence
     * @return boolean
     */
    public function stream_seek($offset, $whence = SEEK_SET) {
        switch($whence) {
            case SEEK_SET:
                $mapiWhence = STREAM_SEEK_SET;
                break;
            case SEEK_END:
                $mapiWhence = STREAM_SEEK_END;
                break;
            default:
                $mapiWhence = STREAM_SEEK_CUR;
        }
        return mapi_stream_seek($this->mapistream, $offset, $mapiWhence);
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
        return ($this->position >= $this->streamlength);
    }

    /**
     * Truncates the stream to the new size.
     *
     * @param int $new_size
     * @return boolean
     */
    public function stream_truncate($new_size) {
        $this->streamlength = $new_size;
        $this->toTruncate = true;

        if ($this->position > $this->streamlength) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("MAPIStreamWrapper->stream_truncate(): stream position (%d) ahead of new size of %d. Repositioning pointer to end of stream.", $this->position, $this->streamlength));
            $this->position = $this->streamlength;
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
            7               => $this->streamlength,
            'size'          => $this->streamlength,
        );
    }

   /**
     * Instantiates a MAPIStreamWrapper
     *
     * @param mapistream    $mapistream         The stream to be wrapped
     * @param boolean       $truncatehtmlsafe   Indicates if a truncation should be done html-safe - default: false
     *
     * @access public
     * @return MAPIStreamWrapper
     */
     static public function Open($mapistream, $truncatehtmlsafe = false) {
        $context = stream_context_create(array(self::PROTOCOL => array('stream' => &$mapistream, 'truncatehtmlsafe' => $truncatehtmlsafe)));
        return fopen(self::PROTOCOL . "://",'r', false, $context);
    }
}

stream_wrapper_register(MAPIStreamWrapper::PROTOCOL, "MAPIStreamWrapper");
