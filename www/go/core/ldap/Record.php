<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace go\core\ldap;

use Exception;

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
 *
 * @property string $cn
 */
class Record {

	/**
	 * The LDAP connection object holding the link
	 * 
	 * @var Connection 
	 */
	private $connection;
	private $entryId;
	private array $attributes;

	private $objectClass;
	
	protected static $_mapping = false;

	public function __construct(Connection|null $connection = null, $entryId = null) {
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

			$this->objectClass = $this->attributes['objectclass'];
			unset($this->attributes['objectclass']);
		}

		return $this->attributes;
	}

	public function getObjectClass() {
		if(!isset($this->objectClass)) {
			$this->getAttributes();
		}

		return $this->objectClass;
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
	 * Find LDAP records
	 *
	 * @param Connection $connection
	 * @param string $dn
	 * @param string $query
	 * @param int $sizeLimit
	 * @return Result<Record>
	 */
	public static function find(Connection $connection, string $dn, string $query, int $sizeLimit = -1): Result
	{
		go()->debug('Find DN: "'.$dn.'", Query: "' . $query . '"');

		$oldHandler = set_error_handler(function($no, $message, $file, $line) use (&$oldHandler) {
			if(str_contains($message, 'Partial search results returned: Sizelimit exceeded')) {
				return true;
			}
			$oldHandler($no, $message, $file, $line);
			return true;
		});
		try {
			$searchId = ldap_search($connection->getLink(), $dn, $query, [], 0, $sizeLimit);
			restore_error_handler();
			return new Result($connection, $searchId);
		} catch (Exception $e) {
			restore_error_handler();
			throw $e;
		}
	}

	/**
	 * Get the DN of this record.
	 * @return string The distinguished name of an LDAP entity.
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
