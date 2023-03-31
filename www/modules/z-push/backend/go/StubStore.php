<?php

/**
 * This is an email provider.
 * It is used to provide a single Email with a link to complete the 2FA authentication process
 */
class StubStore extends Store {

	const ModifiedDate = '1986-06-07\T00:00:00P';

	public function GetMessage($folderid, $id, $contentparameters)
	{
		$system = \go\core\model\Settings::get();
		$subject = '** 2-factor authentication **';
		$text = "
Hi,

Please login to Group-Office to complete your account setup at:

<a href=\"$system->URL\">$system->URL</a>

Best regards,

$system->title";


		$msg = new SyncMail();

		$msg->subject = $subject;
		$msg->from = '2FA service';
		$msg->read = 0;
		//$msg->messageclass = "IPM.Note";
		$msg->datereceived = time();
		$msg->flag = new SyncMailFlags();
		$msg->flag->flagstatus = SYNC_FLAGSTATUS_ACTIVE;
		$msg->flag->flagtype = "FollowUp";

		$msg->asbody = new SyncBaseBody();
		$msg->asbody->type = SYNC_BODYPREFERENCE_HTML;
		$msg->asbody->truncated = 0;
		$msg->asbody->data = StringStreamWrapper::Open(str_replace("\n", "<br>\r\n", $text), false);
		$msg->asbody->estimatedDataSize = strlen($text);

		return $msg;
	}

	public function GetFolder($id)
	{
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("StubProvider->GetFolder('%s')", $id));
		if($id == "root") {
			$folder = new SyncFolder();
			$folder->serverid = $id;
			$folder->parentid = "0";
			$folder->displayname = "Inbox";
			$folder->type = SYNC_FOLDER_TYPE_INBOX;

			return $folder;
		}
		return false;
	}

	public function StatMessage($folderid, $id)
	{
		return [
			'id' => $id,
			'flags' => 0,
			'mod' => self::ModifiedDate
		];
	}

	public function GetMessageList($folderid, $cutoffdate)
	{
		ZLog::Write(LOGLEVEL_DEBUG, "StubProvider::GetMessageList($folderid, $cutoffdate)");
		return [
			[
				"mod" => self::ModifiedDate,
				"id" => '**INCOMPLETE-2FA**', // uid
				"star" => true,
				"flags" => false,
			]
		];
	}

	public function GetFolderList()
	{
		return [
			[
				'id'=>'INBOX',
				"parent"=>"0",
				"mod"=>'INBOX'
			]
		];
	}

	public function ChangeMessage($folderid, $id, $message, $contentParameters){
		return false; // message is read-only
	}

	public function DeleteMessage($folderid, $id, $contentParameters){
		return false; // message is read-only
	}
}