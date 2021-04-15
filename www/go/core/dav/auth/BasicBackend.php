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

namespace go\core\dav\auth;

use GO;
use go\core\auth\TemporaryState;
use go\core\model\Module;
use go\core\model\User;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\DAV\Exception\Forbidden;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class BasicBackend extends AbstractBasic {
	
	protected $user;
	private $checkModulePermission = 'dav';
	private $checkModulePackage = 'legacy';
	
	public function __construct() {
		$this->setRealm("Group-Office");
	}
	
	protected function validateUserPass($username, $password) {
		
		$user = User::find(['id', 'username', 'password'])->where(['username' => $username, 'enabled' => true])->single();

		/* @var $user User */
		if(!$user) {
			return false;
		}
		
		if(!$user->checkPassword($password)) {
			return false;
		}
		
		if(!Module::isAvailableFor($this->checkModulePackage, $this->checkModulePermission, $user->id)) {
			throw new Forbidden("Module " .$this->checkModulePackage . '/' . $this->checkModulePermission . " not available");
		}
		
		$state = new TemporaryState();
		$state->setUserId($user->id);		
		go()->setAuthState($state);

		go()->debug("Authentication success: ". $user->username);

		$this->user = $user;		

		return true;
	}
	
	public function checkModulePermission($package, $module) {
		$this->checkModulePermission = $module;
		$this->checkModulePackage = $package;
	}

	public function check(RequestInterface $request, ResponseInterface $response)
	{
		$result = parent::check($request, $response);

		if($this->user) {
			$result[1] = $this->principalPrefix . $this->user->username; // fix case insensitive login
		}
		return $result;
	}
}
