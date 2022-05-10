<?php
namespace GO\Calendar\Exception;

class AskPermission extends \Exception {

	public function __construct($message = 'Ask permission', $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}