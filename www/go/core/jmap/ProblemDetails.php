<?php
namespace go\core\jmap;

use go\core\util\StringUtil;

class ProblemDetails extends \go\core\data\Model {

	const ERROR_SERVER_FAIL = "serverFail";
	
	public function __construct($type, $status = null, $detail = null) {
		$this->type = $type;
		$this->status = $status;
		$this->detail = $detail;
		Response::get()->setContentType('application/problem+json; charset=UTF-8');
		Response::get()->setStatus($status, StringUtil::normalizeCrlf($detail, ""));
	}
	public $type;
	public $status;
	public $detail;

}
