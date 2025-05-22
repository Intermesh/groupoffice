<?php
namespace go\core\orm;

use go\core\db\Column;
use go\core\db\Table;
use go\core\db\Connection;

class MappedTable extends Table {

	/**
	 * @see Mapping::$dynamic;
	 * @var bool
	 */
	public $dynamic = false;
	
	/**
	 * The table alias used in the mapping
	 * @var string
	 */
	private $alias;
	
	/**
	 * The keys used to join to other tables
	 * @var array eg ['id' => 'userId']
	 */
	private $keys;
		
	
	private $mappedColumns;
	
	private $constantValues;
	
	/**
	 * When set to true this table will be joined with AND userId = <CURRENTUSERID>
	 * 
	 * @var boolean 
	 */
	public $isUserTable = false;

	/**
	 * When true this will LEFT join when fetched and in the delete query
	 * @var bool
	 */
	public $required = false;


	protected function getCacheKey(): string
	{
		return 'dbColumns_' . $this->dsn . '_' . $this->getName().'_'.$this->getAlias();
	}
	
	/**
	 * Mapped table constructor
	 * 
	 * @param string $name The table name
	 * @param string $alias The table alias to use in the queries
	 * @param array|null $keys If empty then it's assumed the key name is identical in
	 *   this and the last added table. eg. ['id' => 'id']
	 * @params array $columns Leave this empty if you want to automatically build 
	 *   this based on the properties the model has. If you're extending a model 
	 *   then this is not possinble and you must supply all columns you do want to 
	 *   make available in the model.
	 * @params array $constantValues If the table that is joined needs to have 
	 *   constant values. For example the keys are ['folderId' => 'folderId'] but 
	 *   the joined table always needs to have a value 
	 *   ['type' => "foo"] then you can set it with this parameter.
	 */
	public function __construct(string $name, string $alias, array|null $keys = null, array $columns = [], array $constantValues = [], bool $isUserTable = false, Connection|null $conn = null) {

		$this->alias = $alias;
		$this->isUserTable = $isUserTable;

		parent::__construct($name, $conn ?? go()->getDbConnection());

		if (!isset($keys)) {
			$keys = $this->buildDefaultKeys();
		}

		$this->keys = $keys;		
		foreach($this->columns as $col) {
			$col->table = $this;
		}

		$this->mappedColumns = array_filter($this->columns, function($c) use ($columns) {
			return in_array($c->name, $columns);
		});	
		
		$this->constantValues = $constantValues;
	}
	
	
	/**
	 * Get the columns that are mapped.	 
	 * 
	 * @return Column[]
	 */
	public function getMappedColumns(): array
	{
		return $this->mappedColumns;
	}
	
	public function getColumn(string $name) : ?Column {
		$cols = $this->getMappedColumns();
		return $cols[$name] ?? null;
	}
	
	private function buildDefaultKeys(): array
	{
		$keys = [];
		foreach ($this->getPrimaryKey() as $pkName) {
			if($this->isUserTable && $pkName == "userId") {
				continue;
			}

			$keys[$pkName] = $pkName;
		}
		
		return $keys;
	}
	
	/**
	 * Get the constant values.
	 * 
	 * @see __construct()
	 * 
	 * @return array ['col' => 'value']
	 */
	public function getConstantValues(): array
	{
		return $this->constantValues;
	}
	
	/**
	 * Get the table alias used in the mapping
	 * 
	 * @return string
	 */
	public function getAlias() : string {
		return $this->alias;
	}
	
	/**
	 * The keys used to join to other tables
	 * 
	 * @return array eg ['id' => 'userId']
	 */
	public function getKeys(): array
	{
		return $this->keys;
	}

//	public function __serialize()
//	{
//		return array_merge(
//			parent::__serialize(),
//			[
//				'alias' => $this->alias,
//				'keys' => $this->keys,
//				'mappedColumns' => $this->mappedColumns,
//				'constantValues' => $this->constantValues,
//				'isUserTable' => $this->isUserTable
//			]
//		);
//	}
//
//	public function __unserialize($data)
//	{
//		$this->alias = $data['alias'];
//		$this->keys = $data['keys'];
//		$this->mappedColumns = $data['mappedColumns'];
//		$this->constantValues = $data['constantValues'];
//		$this->isUserTable = $data['isUserTable'];
//
//		parent::__unserialize($data);
//	}
}


