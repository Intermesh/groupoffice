<?php
namespace go\modules\community\email;

use go\core;
use go\core\http\Response;
use go\core\model\User;
use go\core\orm\Mapping;
use go\core\orm\Property;

class Module extends core\Module
{

	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}


	public static function getTitle(): string
	{
		return 'E-Mail';
	}

	public function defineListeners()
	{
		parent::defineListeners();
		User::on(Property::EVENT_MAPPING, static::class, 'onMap');
	}

	/**
	 * The preferences are edited in the account settings and read by the client as client.user.emailPreferences
	 */
	public static function onMap(Mapping $mapping)
	{
		$mapping->addHasOne('emailPreferences', model\Preferences::class, ['id' => 'userId'], true);
	}

	protected function rights(): array
	{
		return [
			'mayChangeAccount', // allows EmailAccount/set
		];
	}

	public function downloadAttachment($id, $partId)
	{
		$email = model\Email::find()->select('e.uid, f.name, e.accountId')
			->join('email_map','m', 'm.fk = e.id', 'left')
			->join('email_mailbox','f', 'm.mailboxId = f.id', 'left')
			->where(['e.id' => $id])
			->fetchMode(\PDO::FETCH_OBJ)->single();

		$account = model\EmailAccount::findById($email->accountId);
		$imap = $account->connect();
		$imap->imap->examine($email->name);


		$imap->imap->downloadFile($email->uid, $partId);
	}
	public function downloadSrc($id)
	{
		$email = model\Email::find()->select('e.uid, f.name, e.accountId')
			->join('email_map','m', 'm.fk = e.id', 'left')
			->join('email_mailbox','f', 'm.mailboxId = f.id', 'left')
			->where(['e.id' => $id])
			->fetchMode(\PDO::FETCH_OBJ)->single();

		$account = model\EmailAccount::findById($email->accountId);
		$imap = $account->connect();
		$imap->imap->examine($email->name);
		$mime = $imap->fetch($email->uid)['RFC822'];

		Response::get()
			->setHeader('Content-Type', 'text/plain;charset=utf-8')
			->setHeader("Content-Length", strlen($mime))
			->sendHeaders();

		echo $mime;
	}
}