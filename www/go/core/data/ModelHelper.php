<?php
namespace go\core\data;

/**
 * Some functions to make models work.
 */
class ModelHelper {
		
	/**
	 * Helper function to make setValues work as if the values were applied 
	 * externally. Otherwise it would be possible to set private or protected 
	 * values.
	 * 
	 * @param object $model
	 * @param array $values
	 */
	public static function setValues(Model $model, array $values) {
		foreach ($values as $propName => $value) {
			$model->{$propName} = $value;						
		}
	}
	
	/**
	 * Helper function to get a value from an object externally.
	 * 
	 * @param \go\core\data\Model $model
	 * @param string $propName
	 * @return mixed
	 */
	public static function getValue(Model $model, $propName) {
		return $model->$propName;
	}
}		
