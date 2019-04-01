<?php

namespace go\core\data\convert;

use go\core\fs\File;
use go\core\model\Field;
use go\core\orm\Entity;
use go\core\orm\Relation;

class CSV extends AbstractConverter {
	
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
	
	/**
	 * List headers to exclude
	 * @var string[]
	 */
	public static $excludeHeaders = [];

	public function export(Entity $entity) {
		
		$headers = $this->getHeaders($entity);
		$record = [];
		foreach($headers as $header) {
			$record[$header] = $this->getValue($entity, $header);
		}
		
		return $record;
	}
	
	/**
	 * Get a value for a header
	 * 
	 * @param Entity $values
	 * @param string $header Header name delimited with a . for sub properties. eg. "emailAddresses.email"
	 * @return string
	 */
	protected function getValue(Entity $entity, $header) {
		$path = explode('.', $header);
		
		$v = $entity;
		foreach($path as $seg) {
			
			if(is_array($v)) {
				if(!isset($v[0])) {		
					$v = $v[$seg] ?? "";
				} else
				{
					$a = [];
				
					foreach($v as $i) {
						if(is_array($i)) {
							$a[] = $i[$seg] ?? "";
						} else
						{
							$a[] = $i->$seg ?? "";
						}
					}

					$v = $a;
				}
			}else
			{
				$v = $v->$seg ?? "";
			}
		}
		
		return is_array($v) ? implode($this->multipleDelimiter, $v) : $v;
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
			if(in_array($name, ['createdAt', 'createdBy', 'ownedBy', 'modifiedAt','aclId','filesFolderId', 'modifiedBy'])){
				continue;
			}
			$headers = $this->addSubHeaders($headers, $name, $value);
		}
		if(method_exists($entityCls, 'getCustomFields')) {
			$fields = Field::findByEntity($entityCls::getType()->getId());
			foreach($fields as $field) {
				$headers[] = 'customFields.' . $field->databaseName;
			}
		}
		
		return $headers;
	}
	
	private function addSubHeaders($headers, $header, $prop) {
		
		if(in_array($header, static::$excludeHeaders)) {
			return $headers;
		}
		
		if(!($prop instanceof Relation)) {
			if(!$prop->primary) {
				$headers[] = $header;
			}
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
			$headers =  $this->addSubHeaders($headers, $subheader, $value);
		}	
		
		return $headers;
	}

	protected function exportEntity(Entity $entity, $fp, $index, $total) {

		if ($index == 0) {
			fputcsv($fp, $this->getHeaders($entity));
		}

		$record = $this->export($entity);
		
		fputcsv($fp, $record);
	}

	public function getFileExtension(): string {
		return 'csv';
	}

	public function importFile(File $file, $values = array()) {
		
	}

}
