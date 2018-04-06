<?php
namespace go\modules\community\imapauthenticator\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;
use go\modules\community\imapauthenticator\model;

class Server extends \go\core\jmap\EntityController {
	
	protected function entityClass(): string {
		return model\Server::class;
	}
	
}
