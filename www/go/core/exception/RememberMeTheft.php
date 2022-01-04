<?php
namespace go\core\exception;

/**
 * Throw when an operation was forbidden.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class RememberMeTheft extends \Exception
{
	public function __construct($message = "", $code = 0, $previous = null) {

		if(empty($message)) {
			$message = go()->t("Remember me token theft detected");
		}

		parent::__construct($message, $code, $previous);
	}
}
