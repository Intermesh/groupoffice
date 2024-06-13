<?php



/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Abstract Controller class to be extenden bij controllers that are used for page rendering
 * Can be used for frontend views, cms module, sites module, or other module that need to render webpages
 *
 * @package GO.base.controller
 * @copyright Copyright Intermesh
 * @version $Id AbstractFrontController.php 2012-06-05 10:01:09 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Site\Components;


abstract class Controller extends \GO\Base\Controller\AbstractController {
//	/**
//	 * Frontend action can be accessed without moduel access
//	 * @return array actions that can be accessed withou module access 
//	 */
//	protected function allowWithoutModuleAccess()
//	{
//		return array('*');
//	}
//	/**
//	 * By default allow guest to the frontend
//	 * Override again of pages requere login
//	 * @return array action that can be accessed as guest 
//	 */
//	protected function allowGuests() {
//		return array('*');
//	}
	
	/**
	 * @var StringHelper the name of the layout to be applied to this controller's views.
	 * Defaults to main, meaning no layout will be applied.
	 * This file will be found in the layouts folder of yout theme
	 */
	public $layout = 'main';


	/**
	 * The title of the page
	 * @var StringHelper
	 */
	private $_pageTitle;
	
	/**
	 * the name of the action that is running. Empty string if none
	 * @var StringHelper name of runned action 
	 */
	private $_action ='';
	
	/**
	 * Set the meta description
	 * 
	 * @var StringHelper 
	 */
	protected $description="";
	
	/**
	 * Set the meta keywords
	 * 
	 * @var array 
	 */
	protected $keywords=array();

	public function getPageTitle()
	{
		if ($this->_pageTitle !== null)
			return $this->_pageTitle;
		else
		{
			return $this->_pageTitle = ucfirst($this->_action);
		}
	}


	public function setPageTitle($val)
	{
		$this->_pageTitle = $val;
	}

	/**
	 * Render a view file with layout wrapped
	 * 
	 * @param string $view name of the view to be rendered
	 * @param array $data data tp be extracted om PHP variables
	 * @param boolean $return return rendering result if true
	 * @return string the redering result if $return is true 
	 */
	public function render($view, $data = null) {
		$output = $this->renderPartial($view, $data);
		if (($layoutFile = $this->getLayoutFile($this->layout)) !== false)
			$output = $this->renderFile($layoutFile, array('content' => $output), true);

		\Site::scripts()->render($output);
		
		return $output;
	}

	/**
	 * Renders a view file.
	 * @param string $view name of the view to be rendered
	 * @param array $data data to be extracted info PHP variables and made available to the view
	 * @return type
	 * @throws CException 
	 */
	public function renderPartial($view, $data = null) {
		if (($viewFile = $this->getViewFile($view)) !== false){
			$output = $this->renderFile($viewFile, $data, true);
			return $output;
		}
		else
			throw new \GO\Base\Exception\NotFound('cannot find the requested view ' . $view);
	}

	/**
	 * This extracts the content of $_data_ the be used into the view file
	 * 
	 * @param string $_viewFile_ the path to the viewfile to be rendered
	 * @param array $_data_ contains the data to be used into the view
	 * @param boolean $_return_ true if the rendered contect should be returned
	 * @return string the rendered page 
	 */
	public function renderFile($_viewFile_, $_data_ = null, $_return_ = false)
	{
		// use special variable names here to avoid conflict when extracting data
		if (is_array($_data_))
			extract($_data_, EXTR_PREFIX_SAME, 'data');
		else
			$data = $_data_;
		if ($_return_)
		{
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		}
		else
			require($_viewFile_);
	}

	/**
	 * Returns the path to the viewfile based on the used template and module
	 * It will search for a template first if not found look in the views/site/ folder
	 * the default viewfile provided by the module
	 * @param string $viewName name to the viewfile
	 * @return string path of the viewfile
	 */
	public function getViewFile($viewName)
	{	
		$module = \Site::model()->getSiteModule();

		if( substr($viewName, 0,1) != "/") {
			$classParts = explode('\\',get_class($this));
			$moduleId = strtolower($classParts[1]);
			$viewName = '/'.$moduleId. '/'.$viewName;	
		}
		
		$file = new \GO\Base\Fs\File($module->moduleManager->path() . 'views/site/' . $viewName . '.php');
		if(!$file->exists())
			throw new \Exception("View '$viewName' not found!");
		
		return $file->path();
	}

	/**
	 * Returns the path to the layoutfile based on the used template and module
	 * @param string $layoutName name to the layoutfile
	 * @return string path of the layoutName
	 */
	public function getLayoutFile($layoutName)
	{
		$module = \Site::model()->getSiteModule();
		
		return $module->moduleManager->path() . 'views/site/layouts/' . $layoutName . '.php';
	}

//	/**
//	 * Creates a relative URL for the specified action defined in this controller.
//	 * 
//	 * @param string $route the URL route. 
//	 * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
//	 * @return string the constructed URL
//	 */
//	public function createUrl($route, $params = array(), $relative = true)
//	{
//		$relativeUrl = \Site::urlManager()->createUrl($route, $params);
//		
//		if (!$relative)
//			return \Site::model()->request()->getHostInfo() .$relativeUrl;
//		else
//			return $relativeUrl;
//	}

	/**
	 * Redirect to another page.
	 * 
	 * @param mixed $url String or array with route and params. eg array('site/front/content','slug'=>'booking-succes', 'param1'=>'value')
	 * @param int $statusCode HTTP Status code
	 */
	protected function redirect($url = '', $statusCode = 302)
	{
		if(is_array($url)){
			$route=isset($url[0]) ? $url[0] : '';
			$url = \Site::urlManager()->createUrl($route, array_splice($url,1));
		}
		\Site::request()->redirect($url, true, $statusCode);
	}

	/**
	 * Get the url to return to from session when login failed. This is usually called after login in
	 * @return string the url
	 */
	public function getReturnUrl()
	{
		if (isset(\GO::session()->values['sites']['returnUrl']))
		{
			$returnUrl = \GO::session()->values['sites']['returnUrl'];
			//unset(\GO::session ()->values['sites']['returnUrl']);
			return $returnUrl;
		}
		else
			return \Site::urlManager()->getHomeUrl(); //Homepage
	}
	/**
	 * Return to this url its return value can be used in redirect()
	 * @param type $url
	 */
	public function setReturnUrl($url) {
		if(is_array($url)){
			$route=isset($url[0]) ? $url[0] : '';
			$url = \Site::urlManager()->createUrl($route, array_splice($url,1));
		}
		\GO::session()->values['sites']['returnUrl'] = $url;
	}
	
	/**
	 * Checks if a user is logged in, if the user has permission to the module and if the user has access to a specific action.
	 * 
	 * @param string $action
	 * @return boolean boolean
	 */
	protected function _checkPermission($action){
		
		$allowGuests = $this->allowGuests();
		
		if(!in_array($action, $allowGuests) && !in_array('*', $allowGuests)){			
			//check for logged in user
			if(!\GO::user())
				return false;			
		}
		
		$module = $this->getModule();
		return !$module || \GO::modules()->isInstalled($module->name);
	}

	public function run($action = '', $params = array(), $render = true, $checkPermissions = true)
	{
		try
		{
			if (empty($action))
				$this->_action = $action = strtolower($this->defaultAction);
			else
				$this->_action = $action = strtolower($action);

			$ignoreAcl = in_array($action, $this->ignoreAclPermissions()) || in_array('*', $this->ignoreAclPermissions());
			if($ignoreAcl){		
				$oldIgnore = \GO::setIgnoreAclPermissions(true);				
			}
			
			$this->beforeAction();
			
			if (!$this->_checkPermission($action))
				throw new \GO\Base\Exception\AccessDenied();

			$methodName = 'action' . $action;
			//$this->$methodName($_REQUEST);
			$this->callActionMethod($methodName, $params);
			
			//restore old value for acl permissions if this method was allowed for guests.
			if(isset($oldIgnore))
				\GO::setIgnoreAclPermissions($oldIgnore);
		}
		catch (\GO\Base\Exception\MissingParameter $e){
			echo $this->render('/site/404', array('error' => $e));
		}
		catch (\GO\Base\Exception\AccessDenied $e){
			\GO::debug($e->getMessage());
			\GO::debug($e->getTraceAsString());
			
			if(!\GO::user()){
				//Path the page you tried to visit into lastPath session for redirecting after login
				\GO::session()->values['sites']['returnUrl'] = \Site::request()->getRequestUri();
				$loginpath = array('site/account/login');
				$this->redirect($loginpath);
			}  else {
//				$controller = new \GO\Site\Controller\SiteController();
				echo $this->render('/site/error', array('error' => $e));
			}
			//echo $this->render('error', array('error'=>$e));
		}
		catch (\GO\Base\Exception\NotFound $e){
			header("HTTP/1.0 404 Not Found");
      header("Status: 404 Not Found");
			
			echo $this->render('/site/404', array('error' => $e));
		}
		catch (\Exception $e){
			
			echo $e->getMessage();
			echo $this->render('/site/error', array('error' => $e));
		}
	}
	
	/**
	 * override this methode to execute some code just before calling an action on the controller 
	 */
	protected function beforeAction()
	{
		
	}	
	
	
}
