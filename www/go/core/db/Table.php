<?php
namespace go\core\db;

use go\core\App;
use go\core\data\Model;
use InvalidArgumentException;
use PDO;

/**
 * Class that fetches database column information for the ActiveRecord.
 * It detects the length, type, default and required attribute etc.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Table {

	private static $cache = [];

	private $name;
	protected $columns;
	protected $indexes;

	private $pk = [];

	/**
	 * @var Connection
	 */
	protected $conn;

	protected $dsn;

	/**
	 * Get a table instance
	 *
	 * @param string $name
	 * @param Connection|null $conn
	 * @return self
	 */
	public static function getInstance(string $name, Connection|null $conn = null): Table
	{

		if(!isset($conn)) {
			$conn = go()->getDbConnection();
		}

		$cacheKey = $conn->getDsn() . '-' . $name;
		if(!isset(self::$cache[$cacheKey])) {
			self::$cache[$cacheKey] = new Table($name, $conn);
		}
		
		return self::$cache[$cacheKey];	
	}

	public static function destroyInstance($name, Connection|null $conn = null) {
		if(!isset($conn)) {
			$conn = go()->getDbConnection();
		}

		$cacheKey = $conn->getDsn() . '-' . $name;
		if(isset(self::$cache[$cacheKey])) {
			self::$cache[$cacheKey]->clearCache();
			unset(self::$cache[$cacheKey]);
		}

		App::get()->getCache()->delete('dbColumns_' . $name);
		
	}
	
	public static function destroyInstances() {
		foreach(self::$cache as $i) {
			$i->clearCache();
		}
		self::$cache = [];
	}


	/**
	 * @throws InvalidArgumentException
	 */
	public function __construct(string $name, Connection $conn) {
		$this->name = $name;
		$this->conn = $conn;
		$this->dsn = $conn->getDsn();
		$this->init();

	}	
	
	/**
	 * Gets the name of the table
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	protected function getCacheKey(): string
	{
		return 'dbColumns_' . $this->dsn . '_' . $this->name;
	}

	/**
	 * Clear the columns cache
	 */
	private function clearCache() {
		go()->getCache()->delete($this->getCacheKey());
	}

	private string $createTable;

	private function init() {
		
		if (isset($this->columns)) {
			return;
		}
		
		$cacheKey = $this->getCacheKey();

		if (($cache = App::get()->getCache()->get($cacheKey))) {
			$this->columns = $cache['columns'];
			$this->pk = $cache['pk'];
			$this->indexes = $cache['indexes'] ?? null;
			$this->conn = null;
			return;
		}

		$stmt = $this->conn->query("show create table `" . $this->name . "`;");
		$stmt->setFetchMode(PDO::FETCH_COLUMN, 1);
		$this->createTable = strtolower($stmt->fetch());
		
		$this->columns = [];

		$sql = "SHOW FULL COLUMNS FROM `" . $this->name . "`;";
		
		$stmt = $this->conn->query($sql);
		while ($field = $stmt->fetch()) {
			$this->columns[$field['Field']] = $this->createColumn($field);
		}

		$this->processIndexes($this->name);

		$this->conn = null;

		App::get()->getCache()->set($cacheKey, ['columns' => $this->columns, 'pk' => $this->pk, 'indexes' => $this->indexes]);

	}

	/**
	 * A column name may not have the name of a Record property name.
	 * 
	 * @param string $fieldName
	 * @throws InvalidArgumentException
	 */
	private function checkReservedName(string $fieldName) {
		if(strpos($fieldName, '@') !== false) {
			throw new InvalidArgumentException("The @ char is reserved for framework usage.");
		}
		
		if(property_exists(Model::class, $fieldName)) {
			throw new InvalidArgumentException("The name '$fieldName' is reserved. Please choose another column name.");
		}
	}

	private function createColumn($field): Column
	{
		
		$this->checkReservedName($field['Field']);
		
		if($field['Default'] == "NULL") {
			$field['Default'] = null;
		}
			
		$c = new Column();
		$c->table = $this;
		$c->name = $field['Field'];
		$c->pdoType = PDO::PARAM_STR;
		$c->required = false;
		$c->default = $field['Default'];
		$c->comment = $field['Comment'];
		$c->nullAllowed = strtoupper($field['Null']) == 'YES';
		$c->autoIncrement = str_contains($field['Extra'], 'auto_increment');
		$c->trimInput = false;
		$c->unsigned = stripos($field['Type'], 'unsigned') !== false;
		//remove "unsigned" or any other extra info that might be there.
		$field['Type'] = explode(" ", $field['Type'])[0];

		$c->dataType = strtoupper($field['Type']);
		preg_match('/(.*)\(([1-9].*)\)/', $field['Type'], $matches);
		if ($matches) {
			$c->length  = intval($matches[2]);
			$c->dbType = strtolower($matches[1]);			
		} else {
			$c->dbType = strtolower(preg_replace("/\(.*\)$/", "", $field['Type']));
			$c->length = null;
		}

		preg_match('/@dbType=(\w*)/i', $c->comment, $matches);
		if($matches) {
			$c->dbType = strtolower($matches[1]);
		}
		
//		if($c->default == 'CURRENT_TIMESTAMP') {
//			throw new InvalidArgumentException("Please don't use CURRENT_TIMESTAMP as default mysql value. It's only supported in MySQL 5.6+");
//		}
		
		switch ($c->dbType) {
			case 'int':
			case 'tinyint':
			case 'smallint':
			case 'bigint':
				if ($c->length == 1 && $c->dbType == 'tinyint') {
					//$c->pdoType = PDO::PARAM_BOOL; MySQL native doesn't understand PARAM_BOOL. Doesn't work with ATTR_EMULATE_PREPARES = false.
					$c->pdoType = PDO::PARAM_INT;
					$c->default = !isset($field['Default']) ? null : (bool) $c->default;
				} else {
					$c->pdoType = PDO::PARAM_INT;
					$c->default = $c->autoIncrement || !isset($field['Default']) ? null : intval($c->default);
				}

				break;

			case 'float':
			case 'double':
			case 'decimal':
				$c->pdoType = PDO::PARAM_STR;
				$c->length = 0;
				$c->default = $c->default == null ? null : floatval($c->default);
				break;
				
			case 'varbinary':
			case 'binary':
				$c->pdoType = PDO::PARAM_LOB;
				break;
			
			case 'text':
				if(isset($c->default)) {
					$c->default = trim($c->default, "'\"");
				}
				$c->length = 65535;
				$c->trimInput = true;
				break;
			case 'json':
				$c->length = 4294967295;
				break;
			case 'longtext':
				if(isset($c->default)) {
					$c->default = trim($c->default, "'\"");
				}
				$c->length = 4294967295;
				$c->trimInput = true;

				//might be json in mariadb
				if($this->isJSON($c)) {
					$c->dbType = 'json';
					$c->trimInput = false;
				}
				break;
			case 'mediumtext':
				if(isset($c->default)) {
					$c->default = trim($c->default, "'\"");
				}
				$c->length = 16777215;
				$c->trimInput = true;
				break;

			case 'tinytext':
				if(isset($c->default)) {
					$c->default = trim($c->default, "'\"");
				}
				$c->length = 255;
				$c->trimInput = true;
				break;
			
			default:				
				$c->trimInput = true;
				break;			
		}

		$c->required = is_null($c->default) && $field['Null'] == 'NO' && strpos($field['Extra'], 'auto_increment') === false;

		if ($field['Field'] == 'createdAt' || $field['Field'] == 'modifiedAt' || $field['Field'] == 'createdBy' || $field['Field'] == 'modifiedBy') {
			//don't validate because they will be set by the Record
			$c->required = false;
		}

		return $c;
	}

	private function processIndexes($tableName) {
		$query = "SHOW INDEXES FROM `" . $tableName . "`";

		$unique = [];

		//group keys;
		// ['keyName' => ['col1', 'col2']];

		$stmt = $this->conn->query($query);
		while ($index = $stmt->fetch()) {

			$this->indexes[strtolower($index['Key_name'])] = $index;

			if ($index['Key_name'] === 'PRIMARY') {

				$this->columns[$index['Column_name']]->primary = true;
				$this->pk[] = $index['Column_name'];
				//don't validate uniqueness on primary key
				continue;
			}

			if ($index['Non_unique'] == 0) {
				if (!isset($unique[$index['Key_name']])) {
					$unique[$index['Key_name']] = [];
				}

				$unique[$index['Key_name']][] = $index['Column_name'];
			}
		}

		foreach ($unique as $cols) {
			foreach ($cols as $colName) {
				$this->columns[$colName]->unique = $cols;
			}
		}
	}


	/**
	 * Get index information by name
	 * 
	 * @link https://dev.mysql.com/doc/refman/8.0/en/show-index.html
	 * @return array|null
	 * @param string $name
	 */
	public function getIndex($name) : ?array {
		return $this->indexes[strtolower($name)] ?? null;
	}

	/**
	 * Check if table has an index by the given name
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasIndex(string $name): bool
	{
		return isset($this->indexes[strtolower($name)]);
	}


	
	/**
	 * Get all column names
	 * 
	 * @return string[]
	 */
	public function getColumnNames(): array
	{
		return array_keys($this->getColumns());
	}
	
	
	/**
	 * Check if column exists
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function hasColumn(string $name): bool
	{
		return isset($this->columns[$name]);
	}
	
	/**
	 * Get a column
	 * 
	 * @param string $name
	 * @return Column
	 */
	public function getColumn(string $name): ?Column
	{
		if(!isset($this->columns[$name])) {
			return null;
		}
		
		return $this->columns[$name];
	}
	
	/**
	 * Get the columns of the table
	 * 
	 * The keys of the array are the column names.
	 * 
	 * 
	 * @return Column[]
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}	
	
	/**
	 * Get the auto incrementing column
	 * 
	 * @return Column|boolean
	 */
	public function getAutoIncrementColumn() {
		foreach($this->getColumns() as $col) {
			if($col->autoIncrement) {
				return $col;
			}
		}
		
		return false;
	}

	/**
	 * @throws DbException
	 */
	public function setAutoIncrementValue(int $ai): void
	{
		go()->getDbConnection()
			->exec("ALTER TABLE `" . $this->name ."` AUTO_INCREMENT=" . $ai);
	}
	
	/**
	 * The primary key columns
	 * 
	 * This value is auto detected from the database. 
	 *
	 * @return string[] eg. ['id']
	 */
	public function getPrimaryKey(): array
	{
		return $this->pk;
	}


	/**
	 * Get all table columns referencing the core_blob.id column.
	 *
	 * It uses the 'information_schema' to read all foreign key relations.
	 * So it's important that every blob is saved in a column with a 'RESTRICT'
	 * foreign key relation to core_blob.id. For example:
	 *
	 * ```
	 * ALTER TABLE `addressbook_contact`
	 *    ADD CONSTRAINT `addressbook_contact_ibfk_2` FOREIGN KEY (`photoBlobId`) REFERENCES `core_blob` (`id`);
	 * ```
	 * @link https://groupoffice-developer.readthedocs.io/en/latest/blob.html
	 * @return array [['table'=>'foo', 'column' => 'blobId']]
	 */
	public function getReferences(string $key = 'id'): array
	{

		$cacheKey = "table-refs-" . $this->getName();
		$refs = go()->getCache()->get($cacheKey);
		if($refs === null) {
			$dbName = go()->getDatabase()->getName();
			go()->getDbConnection()->exec("USE information_schema");


			try {
				//somehow bindvalue didn't work here
				/** @noinspection SqlResolve */
				$sql = "SELECT `TABLE_NAME` as `table`, `COLUMN_NAME` as `column` FROM `KEY_COLUMN_USAGE` where ".
					"table_schema=" . go()->getDbConnection()->getPDO()->quote($dbName) . " AND ".
					"referenced_table_name=" . go()->getDbConnection()->getPDO()->quote($this->getName()) . " and referenced_column_name = " .  go()->getDbConnection()->getPDO()->quote($key);

				$stmt = go()->getDbConnection()->query($sql);
				$refs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			finally{
				go()->getDbConnection()->exec("USE `" . $dbName . "`");
			}

			go()->getCache()->set($cacheKey, $refs);
		}

		return $refs;
	}


//	public function __serialize()
//	{
//		return [
//			'name' => $this->name,
//			'columns' => $this->columns,
//			'indexes' => $this->indexes,
//			'pk' => $this->pk
//		];
//
//	}
//
//	public function __unserialize($data)
//	{
//
//		$this->name = $data['name'];
//		$this->columns = $data['columns'];
//		$this->indexes = $data['indexes'];
//		$this->pk = $data['pk'];
//
//	}
	private function isJSON(Column $c)
	{
		return str_contains($this->createTable, "json_valid(`".strtolower($c->name)."`)");
	}

}
