<?php

namespace go\core\mail;

use go\core\fs\Blob;
use Swift_Attachment;

class Attachment extends Swift_Attachment {

	/**
	 * Provide Blob. Extracts path from blob and returns attachment
	 * 
	 * @param Blob $blob
	 * @return Swift_Attachment
	 */
    public static function fromBlob(Blob $blob) {
        return Swift_Attachment::fromPath($blob->path());
    }

    /**
	 * Provide blob path. returns attachment
	 * 
	 * @param $blob
	 * @return Swift_Attachment
	 */
    public static function fromBlobPath($blob) {
        return Swift_Attachment::fromPath($blob);
    }
}