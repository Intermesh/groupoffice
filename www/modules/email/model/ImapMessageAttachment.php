<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: LinkedEmail.php 7607 2011-09-01 15:38:01Z <<USERNAME>> $
 * @copyright Copyright Intermesh
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */

/**
 * IMAP message attachment model
 */


namespace GO\Email\Model;

use GO\Base\Fs\Folder;
use GO\Base\Fs\File;

class ImapMessageAttachment extends MessageAttachment
{

	/**
	 *
	 * @var Account
	 */
	public $account;
	public $mailbox;
	public $uid;

	public $charset;

	private $_tmpDir;

	/**
	 * Returns the static model of the specified AR class.
	 * Every child of this class must override it.
	 *
	 * @return MessageAttachment the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public function setImapParams(Account $account, $mailbox, $uid)
	{
		$this->account = $account;
		$this->mailbox = $mailbox;
		$this->uid = $uid;
	}

	public function getTempDir(): string
	{
		$this->_tmpDir = \GO::config()->tmpdir . 'imap_messages/' . $this->account->id . '-' . $this->mailbox . '-' . $this->uid . '/';
		if (!is_dir($this->_tmpDir)) {
			mkdir($this->_tmpDir, 0700, true);
		}
		return $this->_tmpDir;
	}

	/**
	 *
	 * @param Folder $targetFolder
	 * @param string $filename Optional
	 * @return
	 */
	public function saveToFile(Folder $targetFolder, $filename = null): File
	{
		if (!isset($filename)) {
			$filename = $this->name;
		}

		$path = $targetFolder->createChild($filename)->path();

		$imap = $this->account->openImapConnection($this->mailbox);
		if($imap->save_to_file($this->uid, $path, $this->number, $this->encoding)) {
			return new File($path);
		};
		throw new \Exception("Error saving attachment to temp folder");
//		return $imap->save_to_file($this->uid, $path, $this->number, $this->encoding, true);
	}

	public function createTempFile()
	{
		if (!$this->hasTempFile()) {
			$tmpFile = new File($this->getTempDir() . File::stripInvalidChars($this->name));
			if (!$tmpFile->exists()) {
				$imap = $this->account->openImapConnection($this->mailbox);
				$imap->save_to_file($this->uid, $tmpFile->path(), $this->number, $this->encoding);
			}
			$this->setTempFile($tmpFile);
			$this->size = $tmpFile->size();
		}

		return $this->getTempFile();
	}

	public function getEstimatedSize(): float
	{
		if ($this->hasTempFile() || $this->encoding !== 'base64') {
			return $this->size;
		} else {
			return ceil($this->size * 0.75);
		}
	}

	public function getData()
	{
		$imap = $this->account->openImapConnection($this->mailbox);
		return $imap->get_message_part_decoded($this->uid, $this->number, $this->encoding, $this->charset, true, false);
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		if ($this->hasTempFile()) {
			return parent::getUrl();
		} else {
			$params = array(
				"account_id" => $this->account->id,
				"mailbox" => $this->mailbox,
				"uid" => $this->uid,
				"number" => $this->number,
				"encoding" => $this->encoding,
				"filename" => $this->name
			);
		}
		return \GO::url('email/message/attachment', $params);
	}


	public function __wakeup()
	{
		//refresh the account model because the password may have been changed
		$this->account = Account::model()->findByPk($this->account->id);
	}
}
