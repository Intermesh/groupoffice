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
use go\core\auth\TemporaryState;
use go\core\model\User;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\DAV\Exception\Forbidden;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class BasicBackend extends \go\core\dav\auth\BasicBackend {

	public function check(RequestInterface $request, ResponseInterface $response) {
		$result = parent::check($request, $response);
		
		if($result[0] == true) {
			GO::session()->setCurrentUser($this->user->id);
		}

		return $result;
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
