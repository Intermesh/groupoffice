<?php

namespace go\core\controller;

use go\core\db\Criteria;
use go\core\ErrorHandler;
use go\core\event\EventEmitterTrait;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\orm\Query;
use go\core\jmap\EntityController;
use go\core\util\ArrayObject;
use go\modules\community\addressbook\model\Contact;
use go\core\model\Module;
use go\core\model;

class Principal extends EntityController {

	protected function entityClass(): string {
		return model\Principal::class;
	}

	public function query($params) {
		return $this->defaultQuery($params);
	}

	public function get($params) {
		return $this->defaultGet($params);
	}

	public function set($params) {
		throw new Forbidden();
	}

	public function changes($params) {
		return $this->defaultChanges($params);
	}

	public function getAvailability($params){
 		// tpdp
	}

}
