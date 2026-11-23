<?php
namespace go\core\jmap\exception;

use go\core\exception\UserSafeException;

/**
 * A query was aborted because it ran longer than its maximum execution time
 *
 * Sent to the client as an error with type "queryTimeout".
 *
 * @see \go\core\db\Query::maxExecutionTime()
 */
class QueryTimeout extends \Exception implements UserSafeException {
	public function __construct(string|null $message = null) {
		parent::__construct($message ?? go()->t("The search took too long. Please use a longer or more specific search text."));
	}
}
