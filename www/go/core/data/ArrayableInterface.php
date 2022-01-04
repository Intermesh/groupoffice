<?php
namespace go\core\data;
/**
 * Support conversion to array
 * 
 * Object must implement a toArray function so they can be converted into an 
 * array for the API's JSON or XML output.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
interface ArrayableInterface {

	/**
	 * Convert this model to an array for the API
	 *
	 * @param string[]|null $properties
	 * @return array Key value array of the object properties
	 */
	public function toArray(array $properties = null): array;
}
