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
use go\core\auth\Authenticate;
use go\core\auth\TemporaryState;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\DAV\Exception\Forbidden;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class BasicBackend extends AbstractBasic {
	
	private $user;
	public $checkModuleAccess='dav';
	
	public function __construct() {
		$this->setRealm(GO::config()->product_name);
	}
	
	public function check(RequestInterface $request, ResponseInterface $response) {
		$result = parent::check($request, $response);
		
		if($result[0]==true) {
			
			GO::debug("Login basicauth successfull as ".$this->user->username);
			
			GO::session()->setCurrentUser($this->user->id);
		}
		if($this->user) {
			$result[1] = $this->principalPrefix . $this->user->username; // fix case insensitive login
		}
		return $result;
	}

//	For basic auth
	protected function validateUserPass($username, $password) {

		$auth = new Authenticate();
		$this->user = $auth->passwordLogin($username, $password);
		if(!$this->user) {
			return false;
		}

		$state = new TemporaryState();
		$state->setUserId($this->user->id);
		go()->setAuthState($state);
		
		$davModule = Module::model()->findByName($this->checkModuleAccess, false, true);
		if(!$davModule || !\GO\Base\Model\Acl::getUserPermissionLevel($davModule->aclId, $this->user->id))
		{
			$errorMsg = "No '".$this->checkModuleAccess."' module access for user '".$this->user->username."'";
			\GO::debug($errorMsg);
			throw new Forbidden($errorMsg);
		}

		return true;
	}
	
	/**
	 * for old framework to work in GO::session()
	 * 
	 * @param \GO\Dav\Auth\User $user
	 */
	private function oldLogin($userId) {
		if(!defined('GO_NO_SESSION')) {
			define("GO_NO_SESSION", true);
		}
		$_SESSION['GO_SESSION'] = ['user_id' => $userId];
	}
}
