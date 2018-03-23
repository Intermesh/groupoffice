<?php
namespace go\modules\community\ldapauthenticator\controller;

use go\core\jmap\Controller;
use go\core\jmap\Response;
use go\modules\community\ldapauthenticator\model;

class LdapAuthServer extends \go\core\jmap\EntityController {
	
	protected function entityClass(): string {
		return model\LdapAuthServer::class;
	}
	
}
