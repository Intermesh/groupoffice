<?php
class GO_EVENTS
{
	var $listeners;
	
	public function __construct(){
		global $GO_CONFIG;

//		if(!defined('NO_EVENTS')){
//			
//			require_once($GO_CONFIG->root_path.'GO.php');
//
//			/*
//			 * Cache listerner in a file because scanning all the modules for
//			 * listeners is a heavy job.
//			 */
//			$cache_file = GO::config()->getCacheFolder()->path().'/listeners.txt';
//			if(!$GLOBALS['GO_CONFIG']->debug && file_exists($cache_file)){
//				$this->listeners = unserialize(file_get_contents($cache_file));
//			}
//
//			if(!$this->listeners)// || $GLOBALS['GO_CONFIG']->debug))
//			{
//				$this->load_listeners();
//				if(!empty($GLOBALS['GO_CONFIG']->db_user)){
//					//File::mkdir(dirname($cache_file));
//					file_put_contents($cache_file, serialize($this->listeners));
//				}
//			}
//		}
	}
	/**
	 * Scans all modules and looks for listerners. This method uses a lot of memory so should be avoided.
	 * It's generally only called the first time Group-Office loads.
	 *
	 * @global <type> $GO_MODULES
	 */
	public function load_listeners(){
		global $GO_MODULES;
		
		$this->listeners = array();
					
		foreach($GLOBALS['GO_MODULES']->modules as $module)
		{			
			/*$file = $module['class_path'].$module['id'].'.class.inc';

			if(!file_exists($file))
			{*/
				$file = $module['class_path'].$module['id'].'.class.inc.php';
			//}
			if(file_exists($file))
			{
				require_once($file);
				if(class_exists($module['id'], false))
				{				
					$class = new $module['id'];
					$method = '__on_load_listeners';
					if(method_exists($class, $method))
					{						
						$class->$method($this);						
					}
				}
			}
		}
		//$_SESSION['GO_SESSION']['event_listeners']=$this->listeners;
	}
		
	public function add_listener($event, $file, $class, $method){
		if(!isset($this->listeners[$event]))
		{
			$this->listeners[$event]=array();
		}
		$this->listeners[$event][]=array(
			'file'=>$file,
			'class'=>$class,
			'method'=>$method
		);
		
		//go_debug('Adding listener: '.$class.'::'.$method);
	}

	public function remove_listener($event, $class, $method){

		$found = false;
		$new_listeners = array();
		foreach($this->listeners[$event] as $l){
			if($l['class']!=$class || $l['method']!=$method)
				$new_listeners[]=$l;
			else
				$found=true;
		}
		$this->listeners[$event]=$new_listeners;

		return $found;
	}
	
	public function fire_event($event, $args=array())
	{		
		//didn't work with references :(
		//$args = func_get_args();
		//array_shift($args);
		
		if(isset($this->listeners[$event]))
		{
			foreach($this->listeners[$event] as $listener)
			{
				require_once($listener['file']);
				go_debug('Firing listener: '.$listener['class'].'::'.$listener['method']);

				$method = !empty($listener['class']) ? array($listener['class'], $listener['method']) : $listener['method'];
				call_user_func_array($method,$args);
			}		
		}		
	}
}