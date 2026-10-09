<?php
namespace go\core\orm\exception;

use go\core\exception\UserSafeException;
use go\core\orm\Entity;

class SaveException extends \Exception implements UserSafeException {
	public function __construct(Entity $entity)
	{
		$cls = get_class($entity) ;
		$message = "Could not save '" . $cls::entityType()->getName() . "'. Validation failed: " . $entity->getValidationErrorsAsString();
		parent::__construct($message);
	}
}