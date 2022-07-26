<?php
namespace go\core\orm;
/**
 * Used for properties that depend on a user table.
 *
 * For example Task alerts
 *
 * @package go\core\orm
 */
class UserProperty extends Property {

	protected $userId;

	protected function init()
	{
		parent::init();

		if($this->isNew() && go()->getAuthState()) {
			$this->userId = go()->getAuthState()->getUserId();
		}
	}

}