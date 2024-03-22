<?php
namespace go\core\db;
class DbException extends \Exception {
	public function __construct(\PDOException $previous = null)
	{
		parent::__construct("Database exception", 0, $previous);
	}

	/**
	 * Check if this was a unique key exception
	 *
	 * @return bool|string The unique key name
	 */
	public function isUniqueKeyException(): bool|string
	{
		//Unique index error = 23000
		if ($this->getPrevious()->getCode() != 23000) {
			return false;
		}

		$msg = $this->getPrevious()->getMessage();
		//App::get()->debug($msg);

		if(preg_match("/key '(.*)'/", $msg, $matches)) {
			return $matches[1];
		}

		return false;
	}
}