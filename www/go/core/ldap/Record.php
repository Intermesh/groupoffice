<?php

namespace go\core\ldap;

/**
 * LDAP record
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
class Record {

	/**
	 * The LDAP connection object holding the link
	 * 
	 * @var Connection 
	 */
	private $connection;
	private $entryId;
	private $attributes;
	
	protected static $_mapping = false;

	public function __construct(Connection $connection = null, $entryId = null) {
		$this->entryId = $entryId;
		$this->connection = $connection;
	}	

	/**
	 * Get all attributes with values in a key value array
	 * 
	 * @return array 
	 */
	public function getAttributes() {

		$keyToLowerCase = true;
		if (!isset($this->attributes)) {
			$attributes = ldap_get_attributes($this->connection->getLink(), $this->entryId);
			//var_dump($attributes);
			for ($i = 0; $i < $attributes['count']; $i++) {
				//echo $attributes[$i]." : ".$attributes[$attributes[$i]]."\n";
				$key = $keyToLowerCase ? strtolower($attributes[$i]) : $attributes[$i];


				switch ($key) {
					case 'jpegphoto': // its base64 data
						$this->attributes[$key] = $attributes[$attributes[$i]];
						break;

					default:
						$this->attributes[$key] = $this->convertUTF8($attributes[$attributes[$i]]);
						break;
				}

				unset($this->attributes[$key]['count']);
			}
			unset($this->attributes['objectclass']);
		}

		return $this->attributes;
	}

	private function convertUTF8($attr) {
		if (is_array($attr)) {
			$new = array();
			foreach ($attr as $key => $val) {
				$new[$key] = $this->convertUTF8($val);
			}
		} else {
			$new = \go\core\util\StringUtil::cleanUtf8($attr);
		}

		return $new;
	}	

	/**
	 * 
	 * @param \go\core\ldap\Connection $connection
	 * @param string $dn
	 * @param string $query
	 * @return static[]
	 */
	public static function find(Connection $connection, $dn, $query) {
		
		go()->debug('Find DN: "'.$dn.'", Query: "' . $query . '"');
		
		$searchId = ldap_search($connection->getLink(), $dn, $query);
		
		return new Result($connection, $searchId);
	}

	/**
	 * Get the DN of this record.
	 * @return StringHelper The distinguished name of an LDAP entity.
	 */
	public function getDn() {
		return ldap_get_dn($this->connection->getLink(), $this->entryId);
	}
	
//	public function __set($name, $value) {
//		$this->toArray();
//		$this->_attributes[$name] = $value;
//	}

	public function __get($name) {
		$this->getAttributes();
		$name = strtolower($name);
		return $this->attributes[$name] ?? null;
	}

	public function __isset($name) {
		$var = $this->$name;
		return isset($var);
	}
//
//	/**
//	 * Save the attributes that are in the map or extraVar array to ldap dir
//	 *
//	 * @return Boolean true if there are any modifications done
//	 */
//	public function save() {
//
//		$entries = $this->toArray();
//
//		$link = $this->connection->getLink();
//		$dn = $this->getDn();
//
//		GO::debug($entries);
//		return @ldap_modify($link, $dn, $entries);
//	}

}
