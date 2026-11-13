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

/**
 * @extends EntityController<\go\core\model\Principal>
 */
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

	/**
	 * Get the busy periods of a principal. Returns an empty list when the calendar module is not installed.
	 *
	 * @apiParam string id Required. ID of the principal: a user ID or "Calendar:<id>" for a resource calendar
	 * @apiParam string start Required. Start date and time of the period
	 * @apiParam string end Required. End date and time of the period
	 * @apiResponse {list: object[]} The busy periods
	 * @apiPermission Checked in BusyPeriod::fetch(): only events in calendars on which the user has at least free/busy permission (Acl::applyToQuery() with Calendar::LEVEL_FREEBUSY).
	 */
	public function getAvailability($params){
 		if(Module::isInstalled('community', 'calendar')) {
			 return \go\modules\community\calendar\Module::getAvailability(
				 $params['id'],
				 $params['start'],
				 $params['end']
			 );
		}
		 return ['list'=>[]]; // always empty
	}

}
