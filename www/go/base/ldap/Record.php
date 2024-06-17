<?php
namespace GO\Base\Ldap;

use GO;
use GO\Base\Model;

class Record extends Model{

	/**
	 * The LDAP connection object holding the link
	 * 
	 * @var Connection 
	 */
	protected $_ldapConn;
	
	protected $_entryId;
	
	protected $_attributes;
	

	private $_validAttributes;
	
	protected static $_mapping = false;
	
	public function __construct(Connection $ldapConn = null, $entryId= null) {

		$this->_entryId=$entryId;
		$this->_ldapConn= ($ldapConn!==null) ? $ldapConn : Connection::getDefault();
		
	}
	
	/**
	 * Get all attributes with values in a key value array
	 * 
	 * @return array 
	 */
	public function getAttributes(){
		
		$keyToLowerCase=true;
		if(!isset($this->_attributes)){
			$attributes = ldap_get_attributes($this->_ldapConn->getLink(), $this->_entryId);
			//var_dump($attributes);
			for($i=0;$i<$attributes['count'];$i++){
				//echo $attributes[$i]." : ".$attributes[$attributes[$i]]."\n";
				$key = $keyToLowerCase ? strtolower($attributes[$i]) : $attributes[$i];
				
				
				switch ($key) {
					case 'jpegphoto': // its base64 data
						$this->_attributes[$key] = $attributes[$attributes[$i]];
						break;
					
					default:
						$this->_attributes[$key] = $this->_convertUTF8($attributes[$attributes[$i]]);
						break;
				}
				
				unset($this->_attributes[$key]['count']);
			}
			unset($this->_attributes['objectclass']);
		}
		
		return $this->_attributes;
	}
	
	private function _convertUTF8($attr) {
		if(is_array($attr)) {
			$new = array();
			foreach($attr as $key => $val) {
				$new[$key] = $this->_convertUTF8($val);
			}
		}
		else {
			$new = GO\Base\Util\StringHelper::clean_utf8($attr);
		}
		
		return $new;
    }
	
	public function getAttribute($name) {
		$mapping = static::getMapping();
		$key = isset($mapping[$name]) ? $mapping[$name]:$name;
		$attributes = $this->getAttributes();
		if(isset($attributes[$key])) {
			return $attributes[$key];
		}
		else
			return "##".$key;
	}
	
	public function setAttributes($attributes) {
		$mapping = static::getMapping();

		foreach($attributes as $key => $value) {
			$key = isset($mapping[$key]) ? $mapping[$key]:$key;
			if($this->hasAttribute($key)) {
				
				if(is_string($value)){
					if(substr($value, 0,1)==='[') //set json array as php array
						$value = json_decode($value);
					//if(!is_array($value))
					//	$value = array(0=>$value);
					$this->_attributes[$key] = $value;
				}else
				{
					$this->_attributes[$key] = array();
					
					foreach($value as $el){
						if(!empty($el)){
							$this->_attributes[$key][]=$el;
						}
					}
				}
			}
			
		}
	}
	
	/**
	 * check if the given attribute is valid for the currect mappign
	 * $return boolean true when the attribute exists
	 */
	public function hasAttribute($attribute) {
		if(empty($this->_validAttributes)) {
			$extraVarKeys = array(); 
			foreach(static::getExtraVars() as $vars) {
				if(isset($vars[1])){ //Key
					
					$key = str_replace('[]','', $vars[1]);
					$extraVarKeys[$key] = $key;
				}
			}
			//Attributes that are not in the objectClass can not be set
			//$validAttributes = array_merge($extraVarKeys, static::getMapping());
			$this->_validAttributes = $extraVarKeys;
		}
		return isset($this->_validAttributes[$attribute]);
	}
	
	/**
	 * Implement attribute mapping in subclass
	 * @return array key value to map to groupoffice record
	 */
	public static function getMapping() {
		return array('username' => 'uid');
	}
	
	/**
	 * Can be overwritten to add extra attributes
	 */
	public static function getExtraVars() {
		return array();
	}
	
	public static function find($query, $dn=null) {
		if($dn===null)
			$dn = GO::config()->ldap_peopledn;

		$record = new static();
		$mapping = $record->getMapping();
		
		GO::debug("LDAPAUTH: Search DN: ".$dn." Query: ". $query);

		$result = $record->_ldapConn->search($dn, $query);
		$result->fetchClass= get_called_class();
		return $result->fetch();
	}
	
	/**
	 * Get the DN of this record.
	 * @return string The distinguished name of an LDAP entity.
	 */
	public function getDn(){
		return ldap_get_dn($this->_ldapConn->getLink(),$this->_entryId);
	}
	
	public function __get($name){
		$this->getAttributes();
		$name = strtolower($name);
		if(!isset($this->_attributes[$name][0])){
			return null;
		}  else {
			return $this->_attributes[$name];
		}
	}
	
	public function __isset($name) {
		$var = $this->$name;
		return isset($var);
	}
	
	/**
	 * Save the attributes that are in the map or extraVar array to ldap dir
	 *
	 * @return Boolean true if there are any modifications done
	 */
	public function save() {
		
		$entries = $this->getAttributes();
		
		$link = $this->_ldapConn->getLink();
		$dn = $this->getDn();

		GO::debug($entries);
		return @ldap_modify($link, $dn, $entries);
	}
	
	public function getError() {
		return ldap_error($this->_ldapConn->getLink());
	}
}
