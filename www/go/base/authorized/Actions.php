<?php


/**
 * This class keeps track of the authorisations that are made for some 
 * controller actions that need to be available for sessions that don't have a 
 * user logged in.
 * 
 * For example: The action to process plupload uploads should be authorised for 
 * users that have created a ticket and are not logged in.
 */

namespace GO\Base\Authorized;


class Actions{
	
	/**
	 * Check if the current session is authorized to process an controller action.
	 * 
	 * @param StringHelper $name
	 * @return boolean is authorisation granted or not.
	 */
	public static function isAuthorized($name){
		if(!empty(\GO::session()->values['Authorized'])){
			if(in_array($name, \GO::session()->values['Authorized'])){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Set an authorization for an action so the current session is authorized to 
	 * process the action.
	 * 
	 * @param StringHelper $name 
	 */
	public static function setAuthorized($name){
		if(empty(\GO::session()->values['Authorized']))
			\GO::session()->values['Authorized'] = array();
		
		\GO::session()->values['Authorized'][] = $name;
	}
}
