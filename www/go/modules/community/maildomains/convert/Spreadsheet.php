<?php

namespace go\modules\community\maildomains\convert;

use go\core\util\Password;
use go\modules\community\maildomains\model\Mailbox;
use go\modules\community\pwned\model\Pwned;

final class Spreadsheet extends \go\core\data\convert\Spreadsheet
{

	/**
	 * @return void
	 */
	protected function init()
	{
		parent::init();
		$this->addColumn('password', go()->t("Password"));
	}

	/**
	 * @inheritDoc
	 */
	public static function supportedExtensions(): array
	{
		return ['csv'];
	}


	/**
	 * Password resets galore!
	 *
	 * @param Mailbox $mailbox
	 * @return string
	 * @throws \Exception
	 */
	public function exportPassword(Mailbox $mailbox): string
	{
		$pwndAvailable = go()->getModule("community", "pwned");
		if ($pwndAvailable) {
			$pwnd = new Pwned();
		}

		do {
			$passwd = Password::generateRandom();
			$validPwd = (!$pwndAvailable || !$pwnd->hasBeenPwned($passwd));
		} while (!$validPwd);
		// As mailboxes are opened read-only, we need to reopen it RW. Save the newly generated password.
		$m = Mailbox::findById($mailbox->id);
		$m->setPassword($passwd);
		$m->save();
		return $passwd;
	}
}