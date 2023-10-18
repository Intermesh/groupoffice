<?php
namespace go\core;

trait SingletonTrait {
	protected function __construct() {
		
	}
	
	private static $instances = [];
	
	/**
	 * 
	 * @return static
	 */
	public static function get() {		
		$cls = static::class;
		if(!isset(self::$instances[$cls])) {
			self::$instances[$cls] = new static;
		}
		
		return self::$instances[$cls];
	}
	
	protected static function set(self $instance) {
		self::$instances[static::class] = $instance;
	}
	
	protected static function isInitialized() {
		return isset(self::$instances[static::class]);
	}

	public static function destroy() {

		unset(self::$instances[static::class]);

	}
}
