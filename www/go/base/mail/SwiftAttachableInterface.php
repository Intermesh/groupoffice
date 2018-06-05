<?php

namespace GO\Base\Mail;

// Declare the interface 'SwiftAttachableInterface'
interface SwiftAttachableInterface {
	public function getAttachment($altName=null);
}
