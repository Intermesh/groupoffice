<?php

namespace go\modules\community\serverclient\controller;

use go\core\jmap\Response;
use go\core\model\Acl;
use go\core\model\User;
use go\modules\community\serverclient\model\MailDomain;
use go\modules\community\serverclient\Module;

class Serverclient extends \go\core\Controller
{
	/**
	 * save mailbox on server when the user is saved
	 *
	 * password is synchronized in user save event
	 *
	 * @param array $params
	 * @return void
	 * @see Module::onUserSave
	 * @throws \Exception
	 */
	public function setMailbox(array $params)
	{

		$user = User::findById($params['userId']);
		if ($user->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			$postfixAdmin = new MailDomain($params['password']);
			foreach ($params['domains'] as $domain) {
				if (empty($domain)) {
					continue;
				}
				$postfixAdmin->addMailbox($user, $domain);
				$postfixAdmin->addAccount($user, $domain);
			}
		}

		Response::get()->addResponse(['success' => true]);
	}

}