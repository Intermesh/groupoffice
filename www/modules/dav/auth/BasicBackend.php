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

use GO;
use GO\Base\Model\Module;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\DAV\Exception\Forbidden;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class BasicBackend extends AbstractBasic {
	
	private $_user;
	public $checkModuleAccess='dav';
	
	public function __construct() {
		$this->setRealm(GO::config()->product_name);
	}
	
	public function check(RequestInterface $request, ResponseInterface $response) {
		$result = parent::check($request, $response);
		
		if($result[0]==true) {
			
			GO::debug("Login basicauth successfull as ".$this->_user->username);
			
			GO::session()->setCurrentUser($this->_user);
		}
		
		return $result;
	}

//	For basic auth
	protected function validateUserPass($username, $password) {
		$this->_user = GO::session()->login($username, $password, false);
		
		if(!$this->_user) {
			return false;
		}

		$davModule = Module::model()->findByPk($this->checkModuleAccess, false, true);
		if(!$davModule || !\GO\Base\Model\Acl::getUserPermissionLevel($davModule->acl_id, $this->_user->id))
		{
			$errorMsg = "No '".$this->checkModuleAccess."' module access for user '".$this->_user->username."'";
			\GO::debug($errorMsg);
			throw new Forbidden($errorMsg);
		}

		return true;
	}
}
