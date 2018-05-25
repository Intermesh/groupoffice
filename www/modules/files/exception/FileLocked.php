<?php

namespace GO\Files\Exception;


class FileLocked extends \Exception{
	public function __construct() {
		$message = "File is locked";
		return parent::__construct($message);
	}
}
