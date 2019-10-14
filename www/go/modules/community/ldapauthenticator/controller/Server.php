<?php
namespace go\modules\community\ldapauthenticator\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;
use go\modules\community\ldapauthenticator\model;

class Server extends \go\core\jmap\EntityController {
	
	protected function entityClass(): string {
		return model\Server::class;
	}
	
}
