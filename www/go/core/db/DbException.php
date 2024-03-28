<?php
namespace go\core\db;
class DbException extends \Exception {

	public mixed $sql;
	public function __construct(\PDOException $previous, mixed $sql = null)
	{
		$this->sql = $sql;

		$msg = "Database exception";
		if(go()->getDebugger()->enabled) {

			$msg .= ", DEBUG: " . $previous->getMessage();

			if(isset($sql)) {
				$msg .= ", Full SQL: " . $sql;
			}
		}

		parent::__construct($msg, 0, $previous);
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

			//mysql 8 prepends table name,  mariadb does not. We don't need it here so we just return col name
			$col = Utils::splitTableAndColumn($matches[1]);

			return $col->name;
		}

		return false;
	}
}