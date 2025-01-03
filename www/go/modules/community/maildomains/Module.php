<?php

namespace go\modules\community\maildomains;

use go\core;
use go\core\model;
use go\core\model\Module as GoModule;
use go\modules\community\maildomains\cron\CheckDns;
use go\modules\community\maildomains\install\Migrator;
use go\modules\community\maildomains\model\Mailbox;
use go\modules\community\maildomains\model\Settings;
use go\modules\community\pwned\model\Pwned;

final class Module extends core\Module
{
	public function getStatus() : string{
		return self::STATUS_BETA;
	}
	public function getAuthor(): string
	{
		return "Intermesh BV";
	}

	public function defineListeners()
	{
		Mailbox::on(Mailbox::EVENT_VALIDATE, static::class, 'onMailboxValidate');
	}

	protected function afterInstall(GoModule $model): bool
	{
		if(\go\core\model\Module::isInstalled('legacy', 'postfixadmin')) {
			$m = new Migrator();
			$m->migrate();
		}


		CheckDns::install("0 0 * * *");

		return parent::afterInstall($model);
	}

	public static function onMailboxValidate(Mailbox $mb)
	{
		if (!go()->getModule("community", "pwned")) {
			return;
		}
		$plain = $mb->plainPassword();
		if(isset($plain)) {

			$pwnd = new Pwned();
			if($pwnd->hasBeenPwned($plain)) {
				$mb->setValidationError("password", core\validate\ErrorCode::INVALID_INPUT, "The new password is invalid because it has been breached.");
			}
		}

	}

	public function getSettings()
	{
		return Settings::get();
	}
}
