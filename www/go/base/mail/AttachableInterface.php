<?php

namespace GO\Base\Mail;

// Declare the interface 'AttachableInterface'
interface AttachableInterface {
	public function getAttachment($altName=null);
}
