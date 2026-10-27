<?php

namespace GO\Base\Db;
use GO;

class PDO extends \PDO{
	public function __construct($dsn = null, $username = null, $passwd = null, $options=[]) {
		
		if(!isset($dsn)) {
			$dsn = "mysql:host=".\GO::config()->db_host.";dbname=".\GO::config()->db_name.";port=".\GO::config()->db_port;
		}
		
		if(!isset($username)) {
			$username = \GO::config()->db_user;
		}
		
		if(!isset($passwd)) {
			$passwd = \GO::config()->db_pass;
		}
				
		$initCmd = "SET NAMES " . GO::config()->db_charset . ", sql_mode='TRADITIONAL'";
		if(version_compare(PHP_VERSION, '8.5.0') >= 0) {
			$options[\PDO\Mysql::ATTR_INIT_COMMAND] = $initCmd;
		} else {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = $initCmd;
		}
		
		parent::__construct($dsn, $username, $passwd, $options);
		
		$this->setAttribute(\PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(\PDO::ATTR_PERSISTENT, true);
//		$this->setAttribute( PDO::ATTR_STATEMENT_CLASS, array( 'GO\Base\Db\ActiveStatement', array() ) );

		//todo needed for foundRows
		if(version_compare(PHP_VERSION, '8.5.0') >= 0) {
			$this->setAttribute(\PDO\Mysql::ATTR_USE_BUFFERED_QUERY, true);
		} else {
			$this->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		}
		
	}
}
