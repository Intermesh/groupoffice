<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */


/**
 * Router
 * 
 * The Router class which looks up the controller by the request URL. 
 * URL Should be like index.php?r=module/controller/method&param=value
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base 
 */

namespace GO\Base;


class Router{
	
	/**
	 * Analyzes the request URL and finds the controller.
	 * 
	 * URL Should be like index.php?r=module/controller/method&param=value
	 * 
	 * If a controller consist of two words then the second word should start with
	 * a capital letter.
	 * 
	 */
	
	private $_controller;
	
	private $_action;
	
	private $_r;
	
	/**
	 * Get the controller route. eg. email/message/view
	 * 
	 * @return StringHelper 
	 */
	public function getControllerRoute(){
		return $this->_r;
	}
	
	/**
	 * Get the currently active controller for this request.
	 * 
	 * @return Controller\AbstractController 
	 */
	public function getController(){
		return $this->_controller;
	}
	
	/**
	 * Get the currently processing controller action in lowercase and without the
	 * action prefix.
	 * 
	 * @return StringHelper
	 */
	public function getControllerAction(){
		return $this->_action;
	}	
	
	/**
	 * Runs a controller action with the given params
	 * 
	 * @param array $params 
	 */
	public function runController($params=false){
		
		
		//Handles preflight OPTIONS request
		if (isset($_SERVER['REQUEST_METHOD'])){
			switch(strtoupper($_SERVER['REQUEST_METHOD'])){
				
				case 'OPTIONS':
					header('Content-Type: text/plain');
			
					foreach(\GO::config()->extra_headers as $header){
						header($header);
					}
					\GO::debug("OPTIONS request in controller ".get_class($this));
					exit(0);
					
				case 'HEAD':
					\GO::debug("HEAD request in controller ".get_class($this));
					header('X-PHP-Response-Code: 501', true, 501);
					exit(0);
					
			}
		}
		
		
		if(!$params){
			$params = array_merge($_REQUEST, \GO::request()->post);
		}
						
		$r = !empty($params['r']) ?  explode('/', $params['r']): array();
		$this->_r=isset($params['r']) ? $params['r'] : "";
					
		if(\GO::config()->debug || \GO::config()->debug_log){
			$log = 'Controller route r=';
			if(isset($params['r']))
				$log .= $params['r'];
			else 
				$log = 'No r parameter given';				

			\GO::debug($log);
		}
	
		$first = isset($r[0]) ? ucfirst($r[0]) : 'Auth';

		if(empty($r[2]) && file_exists(\GO::config()->root_path.'controller/'.$first.'Controller.php')){
			//this is a controller name that belongs to the Group-Office framework
			$module='Core';
			$controller=$first;
			$action = isset($r[1]) ? $r[1] : '';
			
		}else
		{
			//it must be pointing to a module
			$module=strtolower($r[0]);
			$controller=isset($r[1]) ? ucfirst($r[1]) : 'Default';
			$action = isset($r[2]) ? $r[2] : '';
		}
		
		$action = strtolower($action);
				
		$controllerClass='GO\\';
		
		if(!empty($module))
			$controllerClass.=ucfirst($module).'\\';
		
		$controllerClass.='Controller\\'.$controller.'Controller';
		
		if(preg_match('/[^A-Za-z0-9_\\\\]+/', $controllerClass, $matches)){
			//throw new \Exception("Only these charactes are allowed in controller names: A-Za-z0-9_");
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");

			$errorMsg = "Controller('".Util\StringHelper::encodeHtml($controllerClass)."') not found";

			echo '<h1>404 Not found</h1>';
			echo '<p>'.$errorMsg.'</p>';
			exit();
		}
		
		$this->_action=$action;		
		
		
		if(!class_exists($controllerClass)){
			if(!headers_sent()){
				header("HTTP/1.0 404 Not Found");
				header("Status: 404 Not Found");
			}
			if(empty($_SERVER['QUERY_STRING']))
				$_SERVER['QUERY_STRING']="[EMPTY QUERY_STRING]";

			
			$errorMsg = "Controller('".Util\StringHelper::encodeHtml($controllerClass)."') not found";

			echo '<h1>404 Not found</h1>';
			echo '<p>'.$errorMsg.'</p>';
			exit();
		}
		
		try{
			$this->_controller = new $controllerClass;
			$this->_controller->run($action, $params);		
		}catch(Exception\NotFound $e){
			if(PHP_SAPI != 'cli') {
				header("HTTP/1.0 404 Not Found");
				header("Status: 404 Not Found");
			}
			
			if(empty($_SERVER['QUERY_STRING']))
				$_SERVER['QUERY_STRING']="[EMPTY QUERY_STRING]";
			
			$errorMsg ="Controller action '".  Util\StringHelper::encodeHtml($action)." not found in controller class '".Util\StringHelper::encodeHtml($controllerClass)."'";

			echo '<h1>404 Not found</h1>';
			echo '<p>'.$errorMsg.'</p>';
		}
	}
	

}
