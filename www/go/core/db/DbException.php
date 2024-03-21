<?php
namespace go\core\db;
class DbException extends \Exception {
public function __construct(\PDOException $previous = null)
{
	parent::__construct("Database exception", 0, $previous);
}
}