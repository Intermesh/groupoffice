<?php
namespace go\core\event;

use go\core\orm\Entity;

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
 * See {@see Entity} for an example.
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
	 * You can use this in two different ways:
	 * - When this is called in Module::defineListeners() the listener will be stored and will be used on every request.
	 * - When attaching it in any other place it will only be kept within the current request.
	 *
	 * @param string $event Defined in constants prefixed by EVENT_
	 * @param string $class
	 * @param string $method used for removing the listener.
	 */
	public static function on(string $event, string $class, string $method){
		Listeners::get()->add(static::class, $event, $class, $method);
	}
	
	/**
	 * Fire an event
	 *
	 * If you want to send (non object) variables by references you have to wrap it in an array:
	 *
	 * ['title' => &$title, 'body' => &$body]
	 * 
	 * @param string $event Defined in constants prefixed by EVENT_
	 * @param mixed $args Multiple extra arguments to be passed to the listener functions.
	 * @return boolean
	 */
	public static function fireEvent(string $event, ...$args): bool
	{
		
		if(!go()->eventsEnabled()) {
			return true;
		}
		
		if(!Listeners::get()->fireEvent(static::class, self::class, $event, $args)) {
			return false;
		}		
		
		return true;
	}
}
