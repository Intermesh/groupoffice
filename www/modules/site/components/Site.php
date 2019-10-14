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
 * The Site Launcher Object.
 * The static methods inside this object can be used in all the
 * Models, Views and Controllers that are runned by the site's index.php
 *
 * @package GO.modules.site.components
 * @copyright Copyright Intermesh
 * @version $Id$ 
 * @author Wesley Smits <wsmits@intermesh.nl> 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

//echo "NS:". __NAMESPACE__;

class Site {
	
	/**
	 *
	 * @var \GO\Site\Model\Site 
	 */
	private static $_site;
	
	/**
	 *
	 * @var \GO\Site\Model\Router 
	 */
	private static $_router;
	
	/**
	 *
	 * @var \GO\Site\Components\UrlManager 
	 */
	private static $_urlManager;
	
	/**
	 *
	 * @var \GO\Site\Components\Notifier
	 */
	private static $_notifier;
	
	/**
	 *
	 * @var \GO\Site\Components\Request
	 */
	private static $_request;
	
	/**
	 *
	 * @var \GO\Site\Components\Scripts 
	 */
	private static $_scripts;
	
	/**
	 *
	 * @var \GO\Site\Components\Template 
	 */
	private static $_template;
	
	/**
	 *
	 * @var \GO\Site\Components\AssetManager
	 */
	private static $_assetManager;

	/**
	 *
	 * @var \GO\Site\Components\Config
	 */
	private static $_config;
	
	/**
	 * Get the site model fro the database.
	 * 
	 * @return \GO\Site\Model\Site
	 */
	public static function model(){
		return self::$_site;
	}
	
	public static function controller() {
		return self::router()->getController();
	}
	
	/**
	 * Return's the router that routes an incomming request to a controller
	 * 
	 * @return \GO\Site\Components\Router
	 */
	public static function router(){
		if(!isset(self::$_router))
			self::$_router = new \GO\Site\Components\Router ();
		
		return self::$_router;
	}
	
	/**
	 * Return the config component with all parameter as defined in siteconfig.php
	 * @return \GO\Site\Components\Config
	 */
	public static function config() {
		if(!isset(self::$_config))
			self::$_config = new \GO\Site\Components\Config(self::model());
		return self::$_config;
	}
	
	/**
	 * Get the url manager for this site for createUrl()
	 * 
	 * @return \GO\Site\Components\UrlManager
	 */
	public static function urlManager() {
		if (self::$_urlManager == null) {
			
			self::$_urlManager = new \GO\Site\Components\UrlManager();
			
			$urls = \Site::model()->getConfig()->urls;

			if(!empty($urls))
				self::$_urlManager->rules = $urls;
			else
				self::$_urlManager->rules = array();
			
			self::$_urlManager->init();
		}
		return self::$_urlManager;
	}
	
	/**
	 * Short method for writing urls in your view
	 */
	public static function url($route, $params = array(), $ampersand = '&') {
		return self::urlManager()->createUrl($route, $params, $ampersand);
	}

	/**
	 * Find's the site model by server name or GET param site_id and runs the site.
	 * 
	 * @throws \GO\Base\Exception\NotFound
	 */
	public static function launch($site_id=null) {
		
		if(isset($site_id)){
			self::$_site=\GO\Site\Model\Site::model()->findByPk($site_id,false,true); // Find the website model from its id
		}else{
		
		if(isset($_GET['site_id']))
			GO::session()->values['site_id'] = $_GET['site_id'];

			if(isset(GO::session()->values['site_id']))
				self::$_site=\GO\Site\Model\Site::model()->findByPk(GO::session()->values['site_id'],false,true); // Find the website model from its id
			else
				self::$_site=\GO\Site\Model\Site::model()->findSingleByAttribute('domain', $_SERVER["SERVER_NAME"]); // Find the website model from its domainname
		}

		if(!self::$_site)
			self::$_site=\GO\Site\Model\Site::model()->findSingleByAttribute('domain', '*'); // Find the website model from its domainname

		if(!self::$_site)
			throw new \GO\Base\Exception\NotFound('Website for domain '.$_SERVER["SERVER_NAME"].' not found in database');
		
		\GO::session()->loginWithCookies();
		
		//Login for new framework
		go()->setAuthState(new \go\core\auth\TemporaryState());
//		go()->getAuthState()->setUserId(1);
		if(GO::user()) {
			go()->getAuthState()->setUserId(GO::user()->id);
		}
						
	
		if(!empty(self::model()->language))
			\GO::language()->setLanguage(self::model()->language);

		self::router()->runController();
	}
	

	/**
	 * Adds notification messages to the rendered page.
	 * The message is deleted from the session after it is displayed for the first time
	 * In most cases you want ti use it inside if(Notifier::hasMessage($key))
	 * @return \GO\Site\Components\Notifier
	 */
	public static function notifier() {
		if (self::$_notifier == null)
			self::$_notifier = new \GO\Site\Components\Notifier();
		return self::$_notifier;
	}
	
	/**
	 * Request object for finding requestUri, basePath, HostIno
	 * @return \GO\Site\Components\Request
	 */
	public static function request() {
		if (self::$_request == null)
			self::$_request = new \GO\Site\Components\Request();
		return self::$_request;
	}
	
	
	/**
	 * Component for adding scripts css en meta tags to the head of the rendered result.
	 * use the POS_ constants to define where the scripts should be added
	 * @return \GO\Site\Components\Scripts
	 */
	public static function scripts() {
		if (self::$_scripts == null)
			self::$_scripts = new \GO\Site\Components\Scripts();
		return self::$_scripts;
	}
	
	/**
	 * 
	 * @return \GO\Site\Components\Template
	 */
	public static function template(){
		if (self::$_template == null)
			self::$_template = new \GO\Site\Components\Template();
		return self::$_template;
	}
	
	/**
	 * 
	 * @return \GO\Site\Components\AssetManager
	 */
	public static function assetManager(){
		if (self::$_assetManager == null)
			self::$_assetManager = new \GO\Site\Components\AssetManager();
		return self::$_assetManager;
	}
	
	/**
	 * Get URL to a public template file that is accessible with the browser.
	 * 
	 * @param StringHelper $relativePath
	 * @return StringHelper
	 */
	public static function file($relativePath, $template=true){

		if(!$template){			
			$folder = new \GO\Base\Fs\Folder(\Site::model()->getPublicPath());
			
			$relativePath=str_replace($folder->stripFileStoragePath().'/files/', '', $relativePath);
			return \Site::model()->getPublicUrl().'files/'.\GO\Base\Util\StringHelper::rawurlencodeWithourSlash($relativePath);	
		}else{
			return self::template()->getUrl().\GO\Base\Util\StringHelper::rawurlencodeWithourSlash($relativePath);
		}
	}
	
	
	
	
	/**
	 * Check if a template or asset exists
	 * 
	 * @param StringHelper $relativePath
	 * @return StringHelper
	 */
	public static function fileExists($relativePath, $template=true){
		return file_exists(self::filePath($relativePath, $template));
	}
	
	
	/**
	 * Get Path to a public template file that is accessible with the browser.
	 * 
	 * @param StringHelper $relativePath
	 * @return StringHelper
	 */
	public static function filePath($relativePath, $template=true){
		if(!$template){
			return \Site::model()->getPublicPath().'/files/'.$relativePath;
		}else
		{
			return self::template()->getPath().$relativePath;
		}
	}
	
	
	/**
	 * Get a thumbnail URL for user uploaded files. This does not work for template
	 * images.
	 * 
	 * @param StringHelper $relativePath
	 * @param array $thumbParams
	 * @return StringHelper URL to thumbnail
	 */
	public static function thumb($relativePath, $thumbParams=array("lw"=>100, "ph"=>100, "zc"=>1)) {
		
		$file = new \GO\Base\Fs\File(\GO::config()->file_storage_path.$relativePath);
		
		$thumbParams['filemtime']=$file->mtime();
		$thumbParams['src']=$relativePath;
	
		return \Site::urlManager()->createUrl('site/front/thumb', $thumbParams);
	}
	
}
