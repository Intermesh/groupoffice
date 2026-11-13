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
	 * @apiPermission Write permission on the user. Users have write permission on themselves. Only the domains of the "serverclient_domains" config option.
	 * @param array $params
	 * @return void
	 * @see Module::onUserSave
	 * @throws \Exception
	 */
	public function setMailbox(array $params)
	{

		$user = User::findById($params['userId'] ?? 0);
		if (!$user) {
			throw new \go\core\exception\NotFound();
		}
		if (!$user->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			throw new \go\core\exception\Forbidden();
		}

		// Only the domains configured in "serverclient_domains", the client sends them
		$domains = array_filter((array) ($params['domains'] ?? []));
		$invalid = array_diff($domains, Module::getDomains());
		if (!empty($invalid)) {
			throw new \go\core\jmap\exception\InvalidArguments("Domain not allowed: " . implode(', ', $invalid));
		}

		$postfixAdmin = new MailDomain($params['password']);
		foreach ($domains as $domain) {
			$postfixAdmin->addMailbox($user, $domain);
			$postfixAdmin->addAccount($user, $domain);
		}

		Response::get()->addResponse(['success' => true]);
	}

}