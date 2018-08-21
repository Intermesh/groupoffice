<?php
namespace go\core\data\convert;

/**
 * Abstract convertor class
 * 
 * Used for converting entities into other formats.
 */
abstract class AbstractConverter {
	
	/**
	 * The name of the convertor
	 * 
	 * @return string eg, JSON or CSV
	 */
	public function getName() {
		return array_pop(explode("\\", static::class));
	}
	
	/**
	 * Get the file name extention
	 * 
	 * @return string eg. "csv"
	 */
	abstract public function getFileExtension();
	
	/**
	 * Convert an entity into another format
	 * 
	 * @param array $properties
	 * @return string 
	 */
	abstract public function to(array $properties);
	
	/**
	 * Convert to a properties array from another format
	 * 
	 * @return array
	 */
	abstract public function from($data);
	
	public function getStart() {
		return "";
	}
	
	public function getBetween() {
		return "";
	}
	
	public function getEnd() {
		return "";
	}
}