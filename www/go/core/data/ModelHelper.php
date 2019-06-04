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
			static::setValue($model, $propName, $value);			
		}
	}

	public static function setValue(Model $model, $propName, $value) {

		$props = $model->getApiProperties();

		if(!isset($props[$propName])) {
			throw new \Exception("Not existing property $propName for " . get_class($model));
		}

		if($props[$propName]['setter']) {
			$setter = 'set' . $propName;	
			$model->$setter($value);
		} else if($props[$propName]['access'] == \ReflectionProperty::IS_PUBLIC){
			$model->{$propName} = $value;
		}	else if($props[$propName]['getter']) {
			GO()->warn("Ignoring setting of read only property ". $propName ." for " . get_class($model));
		} else{
			throw new \Exception("Invalid property ". $propName ." for " . get_class($model));
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
		$props = $model->getApiProperties();

		if(!isset($props[$propName])) {
			throw new \Exception("Not existing property $propName for ". $model::getType()->getName());
		}

		if($props[$propName]['getter']) {
			$getter = 'get' . $propName;	
			return $model->$getter();
		} else{
			return $model->{$propName};
		}	
	}
}		
