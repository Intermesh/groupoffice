<?php

namespace go\core\model;

use go\core\http\Request;

class Log extends \go\core\orm\Entity {

	const ACTION_ADD = 'add';
	const ACTION_DELETE = 'delete';
	const ACTION_UPDATE = 'update';
	const ACTION_LOGIN = 'login';
	const ACTION_LOGOUT = 'logout';

	public $id;
	public $user_id;
	public $username;
	public $model_id;
	public $model;
	public $ctime;
	public $user_agent;
	public $ip;
	public $controller_route;
	public $action;
	public $message;
	public $jsonData;
	
	protected static function defineMapping(): \go\core\orm\Mapping {
		return parent::defineMapping()->addTable('go_log');
	}

	protected function init() {
		parent::init();

		if ($this->isNew()) {

			if (PHP_SAPI == 'cli') {
				$this->user_agent = 'cli';
			} else {
				$this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
			}

			$this->ip = Request::get()->getRemoteIpAddress() ?? "";
			$this->controller_route = "JMAP";
			$this->username = go()->getDbConnection()->selectSingleValue('username')->from('core_user')->where('id', '=', go()->getUserId())->single();
			$this->user_id = go()->getUserId() ?? 1;
			$this->ctime = time();
		}
	}

}
