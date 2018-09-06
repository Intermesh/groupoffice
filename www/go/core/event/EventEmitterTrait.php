<?php
namespace go\core\event;


/**
 * Enable events for an object
 * 
 * Note: When adding / removing event listeners you need to run 
 * install/upgrade.php to rebuild the cache.
 * 
 * All objects that implement {@see EventListenerInterface} within the 
 *  application are searched for a static method called 
 * "defineEvents()". In this function you can call
 * 
 * Object::on(Object::EVENT_SOME, self, 'listenerMethod');
 * 
 * Event names should be defined as constants prefixed with EVENT_
 * 
 * See {@see \go\core\orm\Record} for an example.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
trait EventEmitterTrait {
	
	
	/**
	 * Set to true to disable events
	 * 
	 * @var boolean 
	 */
	public static $disableEvents = false;
	
	
	/**
	 * Add a persistent static event listener
	 * 
	 * This listener will be stored and will be used on every request. If you don't
	 * need that then use {@see attach()}
	 * 
	 * @param int $event Defined in constants prefixed by EVENT_
	 * @param callable $fn 
	 * @return int $index Can be used for removing the listener.
	 */
	public static function on($event, $class, $method){		
		Listeners::get()->add(static::class, $event, $class, $method);
	}
	
	/**
	 * Fire an event
	 * 
	 * @param int $event Defined in constants prefixed by EVENT_
	 * @param mixed $args Multiple extra arguments to be passed to the listener functions
	 * @return boolean
	 */
	public static function fireEvent($event){
		
		if(EventEmitterTrait::$disableEvents) {
			return true;
		}
		
		$args = func_get_args();
		
		//shift $event
		array_shift($args);
		
		if(!Listeners::get()->fireEvent(static::class, self::class, $event, $args)) {
			return false;
		}		
		
		return true;
	}
}
