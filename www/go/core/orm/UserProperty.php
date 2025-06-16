<?php
namespace go\core\orm;
/**
 * Used for properties that depend on a user table.
 *
 * For example Task alerts. the $userId property is set automatically on save and when fetching relations
 * it's added to the keys automatically. So each user has its own version of this property.
 *
 * @package go\core\orm
 */
class UserProperty extends Property {

	/**
	 * The owner of this property
	 *
	 * @var int|null
	 */
	protected ?int $userId;

	protected function init()
	{
		parent::init();

		if($this->isNew() && go()->getAuthState()) {
			$this->userId = go()->getAuthState()->getUserId();
		}
	}

}