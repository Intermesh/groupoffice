<?php

namespace GO\Base\Db;
use GO;

class PDO extends \PDO{
	public function __construct($dsn = null, $username = null, $passwd = null, $options=null) {
		
		if(!isset($dsn)) {
			$dsn = "mysql:host=".\GO::config()->db_host.";dbname=".\GO::config()->db_name.";port=".\GO::config()->db_port;
		}
		
		if(!isset($username)) {
			$username = \GO::config()->db_user;
		}
		
		if(!isset($passwd)) {
			$passwd = \GO::config()->db_pass;
		}
				
		
		parent::__construct($dsn, $username, $passwd, $options);
		
		$this->setAttribute(\PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(\PDO::ATTR_PERSISTENT, true);
//		$this->setAttribute( PDO::ATTR_STATEMENT_CLASS, array( 'GO\Base\Db\ActiveStatement', array() ) );

		//todo needed for foundRows
		$this->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true); 
		
		$this->query("SET NAMES " . GO::config()->db_charset);
		$this->query("SET sql_mode='TRADITIONAL'");
	}
}
