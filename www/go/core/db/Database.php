<?php
namespace go\core\db;

use PDO;

class Database {
		
	private $tableNames;	

	private $conn;

	private $name;

	private $user;

	/**
	 *
	 * MariaDB:
	 * 10.5.8-MariaDB-1:10.5.8+maria~focal
	 *
	 * Mysql:
	 * 8.0.22
	 *
	 * @var string
	 */
	private $version;

	public function __construct(Connection $conn = null) {
		$this->conn = $conn ?? go()->getDbConnection();
	}
	
	/**
	 * Check if the current database has a table
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function hasTable(string $name): bool
	{
		return in_array($name, $this->getTableNames());
	}

	private function queryVersion() {
		if(!isset($this->version)) {
			$this->version = $this->conn->query("SELECT VERSION()")->fetchColumn();
		}

		return $this->version;
	}

	public function isMariaDB() {
		return stristr($this->queryVersion(), 'mariadb');
	}

	/**
	 *
	 * @return string eg. 10.0.2
	 */
	public function getVersion(): string
	{
		if($this->isMariaDB()) {
			return explode('-', $this->queryVersion())[0];
		} else{
			return $this->queryVersion();
		}
	}
	
	
	private function getTableNames() {
		if(!isset($this->tableNames)) {
			$stmt = $this->conn->query('SHOW TABLES');
			$stmt->setFetchMode(PDO::FETCH_COLUMN, 0);
			$this->tableNames = $stmt->fetchAll();
		}
		
		return $this->tableNames;
	}
	
	/**
	 * Get all tables
	 * 
	 * @return Table[]
	 */
	public function getTables(): array
	{
		$t = [];
		
		foreach($this->getTableNames() as $tableName) {
			$t[] = Table::getInstance($tableName, $this->conn);
		}
		
		return $t;
	}

	/**
	 * Get a database table
	 * 
	 * @param string $name
	 * @return Table
	 */
	public function getTable(string $name): Table
	{
		return Table::getInstance($name, $this->conn);
	}	
	
	/**
	 * Get database name
	 * 
	 * @return string eg. "user@localhost"
	 */
	public function getUser(): string
	{

		if(!isset($this->user)) {
			$sql = "SELECT USER();";
			$stmt = $this->conn->query($sql);
			$stmt->setFetchMode(PDO::FETCH_COLUMN, 0);
			$this->user = $stmt->fetch();
		}
		return $this->user;
	}



	/**
	 * Get database name
	 * 
	 * @return string
	 */
	public function getName(): string
	{
		if(!isset($this->name)) {
			$sql = "SELECT DATABASE();";
			$stmt = $this->conn->query($sql);
			$stmt->setFetchMode(PDO::FETCH_COLUMN, 0);
			$this->name = $stmt->fetch();
		}

		return $this->name;
	}
	
	/**
	 * Set UTF8 collation
	 */
	public function setUtf8() {
		//Set utf8 as collation default
		$sql = "ALTER DATABASE `" .$this->getName() . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";		
		$this->conn->exec($sql);
	}
}

