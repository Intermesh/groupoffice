<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Auth_Backend.class.inc.php 7752 2011-07-26 13:48:43Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Dav\Auth;

use Sabre;
use Sabre\DAV\Auth\Backend\AbstractDigest;
use GO\Base\Model\User;
use GO\Base\Model\Module;

class Backend extends AbstractDigest {
	
	private $_user;
	
	public function __construct() {
		$this->setRealm('Group-Office');
	}
	
	/**
	 * Check user access for this module
	 * 
	 * @var StringHelper 
	 */
	public $checkModuleAccess='dav';
	
	public function getDigestHash($realm, $username) {
		
		\GO::debug("getDigestHash ".$username);
		
		$user = User::model()->findSingleByAttribute("username", $username);
		
		if($user){
			//check dav module access		
			$davModule = Module::model()->findByName($this->checkModuleAccess, false, true);		
			if(!\GO\Base\Model\Acl::getUserPermissionLevel($davModule->aclId, $user->id))
			{
				$errorMsg = "No '".$this->checkModuleAccess."' module access for user '".$user->username."'";
				\GO::debug($errorMsg);
				throw new Sabre\DAV\Exception\Forbidden($errorMsg);			
			}else{		

				$this->_user = $user;
				
				return $user->digest;
			}		
		}else{
			return null;
		}
	}	
	
	public function check(Sabre\HTTP\RequestInterface $request, Sabre\HTTP\ResponseInterface $response) {
		$result = parent::check($request, $response);
		
		if($result[0]==true) {
			
			\GO::debug("Login successfull as ".$this->_user->username);
			
			\GO::session()->setCurrentUser($this->_user);
		}
		
		return $result;
	}
	

}
