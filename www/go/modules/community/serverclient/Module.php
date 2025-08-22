<?php

namespace go\modules\community\serverclient;

use go\core;
use go\core\App;
use go\core\model\User;
use go\modules\community\serverclient\model\MailDomain;

class Module extends core\Module
{
	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus(): string
	{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return 'Intermesh BV';
	}

	public function defineListeners(): void
	{
		User::on(\go\core\orm\Entity::EVENT_SAVE, static::class, 'onSaveUser');
		go()->on(App::EVENT_SCRIPTS, static::class, 'onLoad');
	}

	/**
	 * Return arrays from configuration. If not configured, the returned array is simply empty.
	 *
	 * @return array
	 */
	public static function getDomains(): array
	{
		$c = go()->getConfig();
		if (!isset($c['serverclient_domains'])) {
			return array();
		}
		if (is_array($c['serverclient_domains'])) {
			return $c['serverclient_domains'];
		}
		return array_map('trim', explode(",", $c['serverclient_domains']));
	}

	public static function onLoad(): void
	{

		echo '<script type="text/javascript">GO.serverclient = {}; GO.serverclient.domains=["' . implode('","', static::getDomains()) . '"];</script>';

	}

	/**
	 *
	 * @param User $user
	 * @return void
	 * @throws \Exception
	 */
	public static function onSaveUser(User $user, bool $wasNew): void
	{
		if ($wasNew) {
			return;
		}

		$domains = self::getDomains();

		if (!empty($user->plainPassword()) && !empty($domains)) {
			$postfixAdmin = new MailDomain($user->plainPassword());
			foreach ($domains as $domain) {
				$postfixAdmin->setMailboxPassword($user, $domain);
			}
		}
	}

}
