<?php

namespace go\core\ldap;

use Countable;
use Iterator;

/**
 * LDAP search result
 * 
 * @example
 * ````
 * $record = Record::find($connection, "ou=people,dc=planetexpress,dc=com", "uid=*");
 * foreach ($record as $record) {
 *	var_dump($record->getAttributes());
 * }
 * 
 * ```
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @author Merijn Schering <mschering@intermesh.nl> 
 */
class Result implements Iterator, Countable {

	/**
	 * The LDAP connection
	 * 
	 * @var Connection 
	 */
	private $connection;
	private $searchId;
	private $index = 0;
	private $entryId;
	private $current;
	
	/**
	 * The class to return for each result record
	 * defaults to Record
	 * @var string 
	 */
	public $fetchClass = Record::class;
					

	public function __construct(Connection $ldapConn, $searchId) {
		$this->searchId = $searchId;
		$this->connection = $ldapConn;
	}

	#[\ReturnTypeWillChange]
	public function current() {		
		return $this->current;
	}

	#[\ReturnTypeWillChange]
	public function key() {
		return $this->index;
	}

	#[\ReturnTypeWillChange]
	public function next() {	
		$this->index++;
		$this->setEntry(ldap_next_entry($this->connection->getLink(), $this->entryId));
	}

	public function rewind(): void {
		$this->index = 0;
		$this->setEntry(ldap_first_entry($this->connection->getLink(), $this->searchId));		
	}
	
	private function setEntry($entryId) {
		$this->entryId = $entryId;
		if (!$this->entryId) {
			$this->current = false;
		} else
		{
			$this->current = new $this->fetchClass($this->connection, $this->entryId);
		}		
	}

	public function valid(): bool {
		return $this->current !== false;
	}

	public function count(): int {
		return ldap_count_entries($this->connection->getLink(), $this->searchId);
	}
	
	/**
	 * Get the next record
	 * 
	 * @return Record|bool
	 */
	public function fetch() {
		if(!isset($this->current)) {
			$this->rewind();
		}
		return $this->current();
	}

}
