<?php
class GoImapStreamWrapper {
    const PROTOCOL = "goimapstream";

		/**
		 *
		 * @var \GO\Base\Mail\Imap 
		 */
    private $imap;
    private $position;
		
		private $size;
   
    /**
     * Opens the stream
     * The string to be streamed is passed over the context
     *
     * @param StringHelper    $path           Specifies the URL that was passed to the original function
     * @param StringHelper    $mode           The mode used to open the file, as detailed for fopen()
     * @param int       $options        Holds additional flags set by the streams API
     * @param StringHelper    $opened_path    If the path is opened successfully, and STREAM_USE_PATH is set in options,
     *                                  opened_path should be set to the full path of the file/resource that was actually opened.
     *
     * @access public
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$opened_path) {
        $contextOptions = stream_context_get_options($this->context);
        if (!isset($contextOptions[self::PROTOCOL]['imap']))
            return false;

        $this->position = 0;

        // this is our stream!
        $this->imap = $contextOptions[self::PROTOCOL]['imap'];
				$this->uid = $contextOptions[self::PROTOCOL]['uid'];
				$this->part_no = $contextOptions[self::PROTOCOL]['part_no'];
				$this->encoding = $contextOptions[self::PROTOCOL]['encoding'];
				
				$this->size = $this->imap->get_message_part_start($this->uid, $this->part_no, true);	

//        $this->stringlength = strlen($this->stringstream);
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("GoImapStreamWrapper::stream_open(): initialized stream length: %d", $this->size));

        return true;
    }

    /**
     * Reads from stream
     *
     * @param int $len      amount of bytes to be read
     *
     * @access public
     * @return StringHelper
     */
    public function stream_read($len) {
			
//			ZLog::Write(LOGLEVEL_DEBUG, sprintf("GoImapStreamWrapper::stream_read"));
			
			$data = $this->imap->get_message_part_line();
			if(!$data){
				ZLog::Write(LOGLEVEL_DEBUG, sprintf("GoImapStreamWrapper::DISCONNECT"));
				$this->imap->disconnect();
				$this->position=$this->size;
				return false;
			}
      $this->position += strlen($data);

			switch (strtolower($this->encoding)) {
				case 'base64':
					$data = base64_decode($data);
					break;
				case 'quoted-printable':
					$data = quoted_printable_decode($data);
					break;

			}
			
//			ZLog::Write(LOGLEVEL_DEBUG, $data);


			return $data;
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
			$eof = $this->position>=$this->size;
			
//			ZLog::Write(LOGLEVEL_DEBUG, sprintf("GoImapStreamWrapper::eof ".var_export($eof, true)));
			return $eof;
    }

    /**
    * Retrieves information about a stream
    *
    * @access public
    * @return array
    */
    public function stream_stat() {
        return array(
//            7               => $this->size,
//            'size'          => $this->size,
        );
    }

   /**
     * Instantiates a GoImapStreamWrapper
     *
     * @param StringHelper    $string     The string to be wrapped
     *
     * @access public
     * @return GoImapStreamWrapper
     */
     static public function Open(\GO\Base\Mail\Imap $imap, $uid, $part_no, $encoding) {
        $context = stream_context_create(array(self::PROTOCOL => array('imap' => $imap, 'uid'=>$uid, 'part_no'=>$part_no,'encoding'=>$encoding)));
        return fopen(self::PROTOCOL . "://",'r', false, $context);
    }
}

stream_wrapper_register(GoImapStreamWrapper::PROTOCOL, "GoImapStreamWrapper");
