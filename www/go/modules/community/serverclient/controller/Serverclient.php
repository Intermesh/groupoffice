<?php

namespace go\modules\community\serverclient\controller;

use go\modules\community\serverclient\model\MailDomain;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Serverclient extends \go\core\Controller {
	
	//save mailbox on server when the user is saved
	// password is sync in user save event
	public function setMailbox($params) {
		
		$user = \go\core\model\User::findById($params['userId']);
		if($user->hasPermissionLevel(\go\core\model\Acl::LEVEL_WRITE)) {
			$postfixAdmin = new MailDomain($params['password']);
			foreach ($params['domains'] as $domain) {
				$postfixAdmin->addMailbox($user,$domain);
				$postfixAdmin->addAccount($user,$domain);
			}
		}

		\go\core\jmap\Response::get()->addResponse(['success' => true]);		
	}

}