<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * Abstract class for al Group-Office controllers.
 * 
 * Any function that starts with action will be publicly accessible by:
 * 
 * index.php?r=module/controllername/functionNameWithoutAction&security_token=1233456
 * 
 * This function will be called with one parameter which holds all request
 * variables.
 * 
 * A security token must be supplied in each request to prevent cross site 
 * request forgeries.
 * 
 * The functions must return a response object. In case of ajax controllers this
 * should be a an array that will be converted to Json or XMl by an Exporter.
 * 
 * If you supply exportVariables in this response object the view will import
 * those variables for use in the view.
 * 
 * @package GO.base.controller
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 * @abstract
 */

namespace GO\Base\Controller;

use Exception;
use GO;
use GO\Base\Data\JsonResponse;
use GO\Base\Exception\AccessDenied;
use GO\Base\Exception\CliOnly;
use GO\Base\Exception\MissingParameter;
use GO\Base\Exception\NotFound;
use GO\Base\Exception\SecurityTokenMismatch;
use GO\Base\Model\Acl;
use GO\Base\Model\Module;
use GO\Base\Observable;
use GO\Base\Util\Http;
use GO\Base\Util\Number;
use GO\Base\View\AbstractView;
use GO\Base\View\FileView;
use GO\Base\View\JsonView;
use ReflectionMethod;


abstract class AbstractController extends Observable {
	
	
	
	/**
	 *
	 * @var StringHelper The module the controller belongs too. 
	 */
	private $_module;
	
	/**
	 * the currently runned action
	 * @var type 
	 */
	private $_action;
	
	/**
	 *
	 * @var StringHelper The default action when none is specified. 
	 */
	protected $defaultAction='Index';

	/**
	 *
	 * @var array See method addPermissionLevel
	 */
	protected $requiredPermissionLevels=array(
			
	);
	
	/**
	 * The currently running action in lowercase without the action prefix.
	 * @var StringHelper 
	 */
	private $_currentAction;
	
	private $_lockedActions=array();
	
	
	
	
	
	/**
	 * The view object that renders the response
	 * If the value is a string the object will be create when the 
	 * render function is called.
	 * Valid strings: 'json', 'file'
	 * @see render()
	 * @var AbstractView|StringHelper
	 */
	protected $view = 'file';

	public function __construct() {
		
		
		$this->init();
		
		if(is_string($this->view)) {
			$name = '\\GO\\Base\\View\\'.ucfirst($this->view).'View';
			$this->view = new $name();
		}
		
		if (!GO::config()->enabled) {
			$this->render('Disabled');
			exit();
		}	
	}
	
	protected function init(){
		
	}
	
	private $lock;
	
	
	/**
	 * Lock the action. When locked it's made sure that the action is only ran by one user at a time.
	 * Useful for maintenance scripts.
	 * 
	 * @throws Exception
	 */
	protected function lockAction(){
		
		$this->lock = new \go\core\util\Lock('action_'.str_replace('/','_', GO::router()->getControllerRoute()));
		
		return $this->lock->lock();
	}
	
	
	/**
	 * Allow guest access
	 * 
	 * Return array with actions (in lowercase and without "action" prefix!) that 
	 * may be accessed by a guest that is not logged in.
	 * Return array('*') to allow access to all controller actions.
	 * 
	 * @return array
	 */
	protected function allowGuests(){
		return array();
	}
	
	/**
	 * Allow access 
	 * 
	 * Return array with actions (in lowercase and without "action" prefix!) that may be accessed without the user having access to the module.
	 * Return array('*') to allow access to all controller actions.
	 * 
	 * @return array
	 */
	protected function allowWithoutModuleAccess(){
		return array();
	}
	
	/**
	 * Return array with actions (in lowercase and without "action" prefix!) that will be run as admin. All ACL permissions are ignored.
	 * Return array('*') to allow access to all controller actions.
	 * 
	 * PLEASE BE CAREFUL! \GO::$ignoreAclPermissions is set to true.
	 * 
	 * @return array
	 */
	protected function ignoreAclPermissions(){
		return array();
	}
	
	/**
	 * Checks a token that is generated for each session.
	 */
	protected function checkSecurityToken(){
		
		//only check token when we are:
		// 1. Not in debug mode
		// 2. There's a logged in user.
		// 3. A route to a controller has been given. Because we don't want to block the default page when entered manually.
		
		if(
						!GO::config()->debug && 
						!GO::config()->disable_security_token_check && 
//						\GO::user() && No longer needed. We only check token when action requires a logged in user
						!empty($_REQUEST['r']) && 
						(!isset($_REQUEST['security_token']) || $_REQUEST['security_token']!=GO::session()->values['security_token'])
			){
			//\GO::session()->logout();			
			throw new SecurityTokenMismatch();

		}
	}	
	
	/**
	 * Get the module object to which this controller belongs.
	 * Returns false if it's a core controller.
	 * 
	 * @return Module 
	 */
	public function getModule(){
		if(!isset($this->_module)){
			$classParts = explode("\\",get_class($this));
			
			$moduleId = strtolower($classParts[1]);
			
			$this->_module = $moduleId=='core' ? false : Module::model()->findByName($moduleId, false, true);			
		}
		
		return $this->_module;
	}
	
	/**
	 * Returns the currenly callen action name;
	 * @return StringHelper
	 */
	public function getAction(){
		return $this->_action;
	}
	
	/**
	 * Includes the file from the views folder
	 * 
	 * @param StringHelper $viewName 
	 * The view will be searched in modules/<moduleid>/views/<view>/<viewName>.php
	 * of /views/<view>/<viewName>.php
	 * 
	 * If it's not found it will fall back on Default.php
	 * 
	 * @param array $data 
	 * An associative array of which the keys become available variables in the view file.
	 */
	protected function render($viewName, $data=array()){
		
		$this->fireEvent('render', [$viewName, &$data]);
		
		return $this->view->render($viewName, $data);		
	}
	
	
	
	
//	protected function renderPartial($data=array()) {
//		
//	}
//	
//	
	/**
	 * Adds a permission check on an acl ID for specific controller actions.
	 * 
	 * Note: In most cases this is not necessary because model's have ACL's in 
	 * most cases which are checked automatically.
	 * 
	 * @param int $aclId
	 * @param int $requiredPermissionLevel See GO_SECURITY constants
	 * @param StringHelper $action By default it applies to all actions but you may specify a specific action here.
	 */
	protected function addPermissionCheck($aclId, $requiredPermissionLevel, $action='*'){
		if(!is_array($action))
			$action = array($action);
		
		foreach($action as $a)
			$this->requiredPermissionLevels[$a]=array('aclId'=>$aclId, 'requiredPermissionLevel'=>$requiredPermissionLevel);
	}
	
	
	
	
	/**
	 * Checks if a user is logged in, if the user has permission to the module and if the user has access to a specific action.
	 * 
	 * @param StringHelper $action
	 * @return boolean boolean
	 */
	protected function _checkPermission($action){
		
		$allowGuests = $this->allowGuests();
		
		if(!in_array($action, $allowGuests) && !in_array('*', $allowGuests)){			
			//check for logged in user
			if(!GO::user()){					
				return false;	
			}
			
			$this->checkSecurityToken();
			
			//check module permission
			$allowWithoutModuleAccess = $this->allowWithoutModuleAccess();
			if(!in_array($action, $allowWithoutModuleAccess) && !in_array('*', $allowWithoutModuleAccess))		
			{
				// return !!go()->getAuthState()->getClassPermissionLevel(static::class);
				$module = $this->getModule();		
				if($module && !$module->permissionLevel)
					return false;
			}
		}
		
		return $this->_checkRequiredPermissionLevels($action);
		
	}
	
	/**
	 * Check the ACL permission levels manually added by addRequiredPermissionLevel();
	 * 
	 * @param StringHelper $action
	 * @return boolean 
	 */
	private function _checkRequiredPermissionLevels($action){
		//check action permission
		if(isset($this->requiredPermissionLevels[$action])){
			$permLevel = Acl::getUserPermissionLevel($this->requiredPermissionLevels[$action]['aclId']);
			return Acl::getUserPermissionLevel($permLevel,$this->requiredPermissionLevels[$action]['requiredPermissionLevel']);
		}elseif($action!='*'){
			return $this->_checkRequiredPermissionLevels('*');
		}else
		{
			return true;
		}
	}
	
	protected function beforeRun($action, $params, $render){
		return true;
	}
	protected function afterRun($action, $params, $render){
		return true;
	}
	
	/**
	 * Runs a method of this controller. If $action is save then it will run
	 * actionSave of your extended class.
	 * 
	 * @param StringHelper $action Without "action" prefix.
	 * @param array $params Key value array of action parameters eg. $params['id']=1;
	 * @param boolean $render Render output automatically. Set to false if you run 
	 *	a controller manually in another controller and want to capture the output.
	 */
	public function run($action='', $params=array(), $render=true, $checkPermissions=true){
		
		if(empty($action))
			$this->_action=$action=strtolower($this->defaultAction);
		else
			$this->_action=$action=strtolower($action);

		$this->_currentAction=$action;


		$methodName='action'.$action;

		if(!method_exists($this, $methodName))
			throw new NotFound();
		
		try {
			if($checkPermissions && !$this->_checkPermission($action)){
				$cls = get_class($this);
				throw new AccessDenied($cls ."::" .$this->getAction());
			}
			
			$ignoreAcl = in_array($action, $this->ignoreAclPermissions()) || in_array('*', $this->ignoreAclPermissions());
			if($ignoreAcl){		
				$oldIgnore = GO::setIgnoreAclPermissions(true);				
			}
			
			$module = $this->getModule();
			
			/**
			 * If this controller belongs to a module and it's the first request to
			 * a module we run the {Module}Module.php class firstRun function
			 * The response is added to the controller's action parameters.
			 */
			if($module && !isset(GO::session()->values['firstRunDone'][$module->name])){
				$moduleClass = "GO\\".ucfirst($module->name)."\\".ucfirst($module->name)."Module";

				if(class_exists($moduleClass)){

					$_REQUEST['firstRun']=call_user_func(array($moduleClass,'firstRun'));
					GO::session()->values['firstRunDone'][$module->name]=true;
				}
			}
			
			//Unset some system parameters not intended for the controller action.
			unset($params['security_token'], $params['r']);
			
			$this->beforeRun($action, $params, $render);

			$response =  $this->callActionMethod($methodName, $params);
			
			$this->afterRun($action, $params, $render);
			
			if($render && isset($response))
				$this->render($action, $response);
			
			//restore old value for acl permissions if this method was allowed for guests.
			if(isset($oldIgnore))
				GO::setIgnoreAclPermissions($oldIgnore);

			return $response;
			
		}
		catch(AccessDenied $e) {
			GO::debug("EXCEPTION: ".(string) $e);

			$response = new JsonResponse();

			$response['success'] = false;

			$response['feedback'] = !empty($response['feedback']) ? $response['feedback']."\r\n\r\n" : '';
			$response['feedback'] .= $e->getMessage();

			$response['exceptionCode'] = $e->getCode();

			$response['exceptionClass'] = get_class($e);
			$response['redirectToLogin']=empty(GO::session()->values['user_id']);

			$this->view->render('Exception', array('response'=>$response));
		}
		catch (Exception $e) {
			
			GO::debug("EXCEPTION: ".(string) $e);
			
			\go\core\ErrorHandler::logException($e);
			
			$response = new JsonResponse();
			
			$response['success'] = false;
			
			$response['feedback'] = !empty($response['feedback']) ? $response['feedback']."\r\n\r\n" : '';
			$response['feedback'] .= $e->getMessage();	
			
			$response['exceptionCode'] = $e->getCode();
					
			$response['exceptionClass'] = get_class($e);

			if($e instanceof SecurityTokenMismatch)
				$response['redirectToLogin']=true;

			if(GO::config()->debug){
				$response['trace']=explode("\n", $e->getTraceAsString());
				//$response['trace']= $e->getTrace();
			}
			
			if($this->isCli()){
				echo "Error: ".$response['feedback']."\n\n";
				if(GO::config()->debug){
					echo $e->getTraceAsString()."\n\n";
				}
				exit(1);
			}

			$this->view->render('Exception', array('response'=>$response));
		}
	}
	
	/**
	 * Calls the controller action with it's parameters. We used to pass all
	 * $_REQUEST args or CLI args as an array to the methods. If you declare the
	 * function as actionMethod($params) this will still work for backwards 
	 * compatibility.
	 * 
	 * If you declare it as actionMethod($test1, $test2, $hasDefault=true) then
	 * the named parameters will be taken from the $_REQUEST args.
	 * 
	 * @param StringHelper $methodName
	 * @param array $params
	 * @return mixed Action method return value
	 * @throws Exception If a required parameter is missing from the $_REQUEST args
	 */
	protected function callActionMethod($methodName, $params){
		
		$method = new ReflectionMethod($this, $methodName);
		
		$rParams = $method->getParameters();
		
//		$param = new ReflectionParameter();
		if(count($rParams)==0){
			return $this->$methodName();
		}elseif(count($rParams)==1 && $rParams[0]->getName()=='params'){
			//backward compatibility mode. Just call the function with all the params in an array.
			return $this->$methodName($params);
		}else
		{			
			//call method with all parameters from the $_REQUEST object.
			$methodArgs = array();
			foreach($rParams as $param){
				if(!isset($params[$param->getName()]) && !$param->isOptional())
					throw new MissingParameter("Missing argument '".$param->getName()."' for action method '".get_class ($this)."->".$methodName."'");
				
				$methodArgs[]=isset($params[$param->getName()]) ? $params[$param->getName()] : $param->getDefaultValue();
				
			}
			return call_user_func_array(array($this, $methodName),$methodArgs);
		}		
	}
	
	/**
	 * Redirect the browser.
	 * 
	 * @param StringHelper $path 
	 */
	protected function redirect($path='', $params=array()){		
		header('Location: ' .GO::url($path, $params));
		exit();
	}
	
	/**
	 * Get the route to this controller. Eg.
	 * 
	 * route = addressbook/contact
	 * 
	 * @return StringHelper 
	 */
	public function getRoute($action=''){
		$arr = explode('\\',get_class($this));
		
		if(isset($arr[3]) && strpos($arr[3], 'Controller')!==false)
			$arr[3] = substr($arr[3],0,0-strlen('Controller')); //cut off Controller from className

		if($arr[1]!='Core')
			$route=lcfirst($arr[1]).'/'.lcfirst($arr[3]);				
		else 
			$route=lcfirst($arr[3]);				
		
		if($action!='')
			$route .= '/'.lcfirst($action);
		
		return $route;
	}	
	
	/**
	 * Check if we are called with the Command Line Interface
	 * 
	 * @deprecated use \GO::environment()->isCli(); instead
	 * 
	 * @return type 
	 */
	public function isCli(){
		return \GO::environment()->isCli();
	}
	
	/**
	 * Check if action is ran on the Command Line Interface
	 * 
	 * @throws CliOnly
	 */
	public function requireCli(){
		if(!$this->isCli())
			throw new CliOnly();
	}
	
	/**
	 * Check if required controller parameters are present
	 * 
	 * @param array $requiredParams
	 * @param array $givenParams
	 * @throws Exception 
	 */
	protected function checkRequiredParameters($requiredParams, $givenParams){
		
		$missingParams = array();
		
		foreach($requiredParams as $param){
			if(empty($givenParams[$param]))
				$missingParams[]=$param;
		}
		
		if(count($missingParams))
			throw new Exception("The following required controller action params are missing: ".implode(",", $missingParams));
				
	}

	protected function checkMaxPostSizeExceeded() {
		if (empty($_POST) && empty($_FILES)) {
			$postMaxSize = Number::configSizeToMB(ini_get('post_max_size'));
			$uploadMaxFileSize = Number::configSizeToMB(ini_get('upload_max_filesize'));

			
			
			$maxFileSize = $postMaxSize > $uploadMaxFileSize ? $uploadMaxFileSize : $postMaxSize;
			
			throw new Exception(sprintf(GO::t("The server did not receive the required parameters from your browser. Probably the maximum filesize for upload of %sMB has been exceeded."),$maxFileSize));
		}
	}
	
//	protected function isAjax(){
//		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
//	}
}
