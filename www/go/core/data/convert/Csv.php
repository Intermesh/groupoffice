<?php

namespace go\core\data\convert;

use go\core\fs\File;
use go\core\model\Field;
use go\core\orm\Entity;
use go\core\orm\Relation;
use go\core\util\DateTime as GoDateTime;
use Sabre\VObject\Property\VCard\DateTime;

/**
 * CSV converter.
 * 
 * Imports a CSV file to entities.
 * 
 * A mapping can be supplied to the JMAP controller or importFile() function. {@see importFile()}
 * 
 * The key is the CSV record index and value the 
 * 	property path. "propName" or "prop.name" if it's a relation.
 * 	If the relation is a has many values can be separated with " ::: ".
 * 
 * For example pass to the options:
 * 
 * [
 * 		"mapping" => [
 * 			"firstName",
 * 			"emailAddresses.email"
 * 	]
 * ]
 */
class Csv extends AbstractConverter {
	
	/**
	 *
	 * @var array
	 */
	private $headers;
	
	/**
	 * Delimits multiple values in single CSV field
	 * 
	 * @var string
	 */
	protected $multipleDelimiter = ' ::: ';

	protected $delimiter = ',';

	protected $enclosure = '"';
	
	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = [];

	protected function init()
	{
		parent::init();

		$user = go()->getAuthState()->getUser(['listSeparator', 'textSeparator']);
		$this->delimiter = $user->listSeparator;
		$this->enclosure = $user->textSeparator;
	}

	public function export(Entity $entity) {
		
		$headers = $this->getHeaders($entity);
		$templateValues = $entity->toTemplate();
		$record = [];
		foreach($headers as $header) {
			$record[$header['name']] = $this->getValue($entity, $templateValues, $header['name']);
		}
		
		return $record;
	}
	
	private $customColumns = [];
	
	/**
	 * Add a custom column to the export and import
	 * 
	 * @example
	 * 
	 *	//override init
	 * 	protected function init() {
	 *		$this->addColumn('status', go()->t("Status", 'sony', 'assets'));
	 *	}
	 * 
	 * @param string $name Column name
	 * @param string $label Column label
	 * @param string $many True if this field value should be converted to an array when importing
	 * @param string $exportFunction Defaults to "export" . ucfirst($name) The function is called with Entity $entity, array $templateValues $columnName
	 * @param string $importFunction Defaults to "import" . ucfirst($name) The import function is called with Entity $entity, $value, array $values
	 */
	protected function addColumn($name, $label, $many = false, $exportFunction = null, $importFunction = null) {
		if(!isset($exportFunction)) {
			$exportFunction = "export".ucfirst($name);
		}
		if(!isset($importFunction)) {
			$importFunction = "import".ucfirst($name);
		}
		
		$this->customColumns[$name] = [
				'name' => $name, 
				'label' => $label, 
				'many' => $many,
				'importFunction' => $importFunction, 
				'exportFunction' => $exportFunction
		];
	}
	
	/**
	 * Get a value for a header
	 * 
	 * @param Entity $entity
	 * @param Array $templateValues
	 * @param string $header Header name delimited with a . for sub properties. eg. "emailAddresses.email"
	 * @return string
	 */
	protected function getValue(Entity $entity, $templateValues, $header) {
		
		if(isset($this->customColumns[$header])) {
			return $this->getCustomColumnValue($entity, $templateValues, $header);
		}
				
		$path = explode('.', $header);
		
		foreach($path as $seg) {
			
			if(is_array($templateValues)) {
				if(!isset($templateValues[0])) {		
					$templateValues = $templateValues[$seg] ?? "";
				} else
				{
					$a = [];
				
					foreach($templateValues as $i) {
						if(is_array($i)) {
							$a[] = $i[$seg] ?? "";
						} else
						{
							$a[] = $i->$seg ?? "";
						}
					}

					$templateValues = $a;
				}
			}else
			{
				$templateValues = $templateValues->$seg ?? "";
			}
		}
		
		return is_array($templateValues) ? implode($this->multipleDelimiter, $templateValues) : $templateValues;
	}
	
	private function getCustomColumnValue(Entity $entity, $templateValues, $header) {
		return call_user_func([$this, $this->customColumns[$header]['exportFunction']], $entity, $templateValues, $header);
	}
	
	private function exportSubFields($record, $v) {
		if(!is_array($v)) {			
			$record[] = $v;
			return $record;
		}
		foreach($v as $key => $subvalue) {
			$record = $this->exportSubFields($record, $subvalue);
		}
		
		return $record;
	}
	
	/**
	 * Get all the CSV field headers
	 * 
	 * Sub properties are delimnited with a . For example "emailAddresses.email".
	 * Multiple values are separated by " ::: ". For example "email1 ::: email2"
	 * @param string $entityCls
	 * @return string[]
	 */
	public final function getHeaders($entityCls) {
		
		if(!isset($this->headers)) {
			$this->headers = $this->internalGetHeaders($entityCls);
		}
		
		return $this->headers;		
	}
	
	/**
	 * Override this to add custom headers
	 * Override "getValue" and "setVallue" too.
	 * 
	 * @param string $entityCls
	 * @return string[]
	 */
	protected function internalGetHeaders($entityCls) {
		//Write headers
		$properties = $entityCls::getMapping()->getProperties();
		$headers = [];

		foreach($properties as $name => $value) {
			//Skip system data
			if(in_array($name, array_merge(['createdAt', 'createdBy', 'ownedBy', 'modifiedAt','aclId','filesFolderId', 'modifiedBy'], array_keys($this->customColumns)))){
				continue;
			}
			$headers = $this->addSubHeaders($headers, $name, $value);
		}
		if(method_exists($entityCls, 'getCustomFields')) {
			$fields = Field::findByEntity($entityCls::entityType()->getId());
			foreach($fields as $field) {
				$headers[] = ['name' => 'customFields.' . $field->databaseName, 'label' => $field->name, 'many' => false];
			}
		}	
		
		return array_merge($headers, array_values($this->customColumns));
	}
	
	private function addSubHeaders($headers, $header, $prop, $many = false) {
		
		if(in_array($header, static::$excludeHeaders)) {
			return $headers;
		}
		
		if(!($prop instanceof Relation)) {
			if(!$prop->primary) {
				//client will define labels if not given. Only custom fields provide label
				$headers[] = ['name' => $header, 'label' => null, 'many' => $many];
			}
			return $headers;
		}

		if($prop->type == Relation::TYPE_SCALAR) {
			$headers[] = ['name' => $header, 'label' => null, 'many' => true];			
			return $headers;
		}
		
		$cls = $prop->entityName;


		$properties = $cls::getMapping()->getProperties();
		
		foreach($properties as $name => $value) {
			
			if(in_array($name, $prop->keys)) {
				continue;
				//don't export relational keys like 'contactId';
			}
			
			$subheader = $header . '.'. $name;
			$headers =  $this->addSubHeaders($headers, $subheader, $value, $prop->type != Relation::TYPE_HAS_ONE);
		}	
		
		return $headers;
	}

	protected function exportEntity(Entity $entity, $fp, $index, $total) {

		if ($index == 0) {
			fputcsv($fp, array_column($this->getHeaders($entity), 'name'), $this->delimiter, $this->enclosure);
		}

		$record = $this->export($entity);
		
		fputcsv($fp, $record, $this->delimiter, $this->enclosure);
	}

	public function getFileExtension(): string {
		return 'csv';
	}
	
	private function recordIsEmpty(array $record) {
		foreach($record as $v) {
			if(!empty($v)) {
				return false;
			}
		}
		return true;
	}

	public function importFile(\go\core\fs\File $file, $entityClass, $params = array())
	{
		$this->delimiter = static::sniffDelimiter($file);

		return parent::importFile($file, $entityClass, $params);
	}
	
	/**
	 * Imports a single record and returns an entity 
	 * 
	 * @param File $file the source file
	 * @param string $entityClass The entity class model. eg. go\modules\community\addressbook\model\Contact
	 * @param array $params Extra import parameters. By default this can only hold 'values' which is a key value array that will be set on each model.
	 * 	$params Can hold "mapping" property. The key is the CSV record index and value the 
	 * 	property path. "propName" or "prop.name" if it's a relation.
	 * 	If the relation is a has many values can be separated with " ::: ".
	 * 
	 * @return int[] id's of imported entities
	 */
	protected function importEntity(Entity $entity, $fp, $index, array $params) {
		
		if($index == 0) {
			$headers = fgetcsv($fp, 0, $this->delimiter, $this->enclosure);
		}
		
		$record = fgetcsv($fp, 0, $this->delimiter, $this->enclosure);
		
		if(!$record || $this->recordIsEmpty($record)) {
			return false;
		}
		
		$mapping = $params['mapping'] ?? $this->getHeaders(get_class($entity));

		$values = $this->convertRecordToProperties($record, $mapping, get_class($entity));
		
		$values = $this->importCustomColumns($entity, $values);

		$this->setValues($entity, $values);

		return $entity;
	}
	
	protected function importCustomColumns(Entity $entity, $values){
		foreach($this->customColumns as $c) {
			call_user_func_array([$this, $c['importFunction']], [$entity, $values[$c['name']] ?? null, &$values, $c['name']]);
			unset($values[$c['name']]);
		}
		return $values;
	}
	
	protected function setValues(Entity $entity, array $values) {
		$cf = $values['customFields'] ?? null;
		unset($values['customFields']);
		$entity->setValues($values);
		//Set custom fields as text. for example select fields not by id but with option text.
		if(isset($cf) && method_exists($entity, 'setCustomFields')) {
			$entity->setCustomFields($cf, true);
		}
	}
	
	/**
	 * Will convert the CSV record to a key value array to use in Entity::setValues();
	 */
	private function convertRecordToProperties($record, $mapping, $entityClass) {
	
		$v = [];
		//create arrays of values that are mapped multiple times.
		
		$h = $this->getHeaders($entityClass);
		$headers = [];
		foreach($h as $i) {
			$headers[$i['name']] = $i;
		}
		
		foreach($mapping as $index => $path) {	
			
			$index = (int) $index;
			$modelClass = $entityClass;		
			$relation = false;
			if(empty($record[$index])) {
				continue;
			}
			$parts = explode('.', $path);
			$propName = array_pop($parts);
			$sub = &$v;
			foreach($parts as $part) {
				if($modelClass && ($relation = $modelClass::getMapping()->getRelation($part))) {
					$modelClass = $relation->entityName;
				}else
				{
					$modelClass = false;
				}
				
				if(!isset($sub[$part])) {
					$sub[$part] = [];
				}
				$sub = &$sub[$part];					
			}
			
			$multiple = ($relation && ($relation->type == Relation::TYPE_ARRAY || $relation->type == Relation::TYPE_MAP)) || !empty($headers[$path]['many']);
			
			if($multiple) {
				if(isset($v[$propName])) {
					$sub[$propName] = array_merge($v[$propName], explode($this->multipleDelimiter, $record[$index]));
				} else
				{
					$sub[$propName] = explode($this->multipleDelimiter, $record[$index]);
				}
			} else
			{
				$sub[$propName] = $record[$index];
			}
		}
		
		
		
		//second pass for multiple values
		foreach($v as $prop => $value) {
			$relation = $entityClass::getMapping()->getRelation($prop);
			if(!$relation || !($relation->type == Relation::TYPE_ARRAY || $relation->type == Relation::TYPE_MAP)) {
				continue;
			}
			
			$new = [];
			foreach($v[$prop] as $subprop => $subval) {
				for($i = 0, $c = count($subval); $i < $c; $i++) {
					if(!isset($new[$i])) {
						$new[$i] = [];
					}
					if(!empty($subval[$i])) {
						$new[$i][$subprop] = $subval[$i];
					}
				}
			}
//			go()->warn($new);
			$v[$prop] = $new;
		}
		
		
		return $v;
	}

	public static function sniffDelimiter(File $file) {
		$fp = $file->open('r');

		$delimiter = ',';
		$enclosure = '"';

		$headers = fgetcsv($fp, 0, $delimiter, $enclosure);
		
		if(!$headers || count($headers) == 1) {
			$delimiter = ';' ;

			$headers = fgetcsv($fp, 0, $delimiter, $enclosure);
			fclose($fp);
			if(!$headers || count($headers) == 1) {
				throw new \Exception("Unable to detect delimiter");
			}
		} else{
			fclose($fp);
		}

		return $delimiter;		
	}
	
	/**
	 * Get headers from CSV
	 * 
	 * @param File $file
	 * @return string[]
	 * @throws \Exception
	 */
	public function getCsvHeaders(File $file) {

		$this->delimiter = static::sniffDelimiter($file);

		$fp = $file->open('r');

		$headers = fgetcsv($fp, 0, $this->delimiter, $this->enclosure);
		
		if(!$headers) {
			throw new \Exception("Could not read CSV file");
		}
		
		return $headers;
	}

}
