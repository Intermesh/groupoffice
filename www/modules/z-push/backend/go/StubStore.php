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
		$subject = go()->t('** 2-Factor authentication **', 'legacy', 'sync');


		$msg = new SyncMail();

		$msg->subject = $subject;
		$msg->from = (string) (new \GO\Base\Mail\EmailRecipients())->addRecipient(go()->getSettings()->systemEmail, go()->getSettings()->title);
        $msg->to = go()->getAuthState()->getUser(['email'])->email;
		$msg->read = 0;
		//$msg->messageclass = "IPM.Note";
		$msg->datereceived = time();
		$msg->flag = new SyncMailFlags();
		$msg->flag->flagstatus = SYNC_FLAGSTATUS_ACTIVE;
		$msg->flag->flagtype = "FollowUp";
        $msg->importance = 1;

        $bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference(), array(SYNC_BODYPREFERENCE_MIME, SYNC_BODYPREFERENCE_PLAIN, SYNC_BODYPREFERENCE_HTML));


        $asBodyData = null;
        switch ($bpReturnType) {
            case SYNC_BODYPREFERENCE_PLAIN:
                $asBodyData = go()->t('2fa-body', 'legacy', 'sync');
                $asBodyData = str_replace(['{URL}','{TITLE}'], [go()->getSettings()->URL, go()->getSettings()->title], $asBodyData);


                break;
            case SYNC_BODYPREFERENCE_HTML:
                $asBodyData = $this->getHtmlBody();

                break;
            case SYNC_BODYPREFERENCE_MIME:
                //we load the mime message and create a new mime string since we can't trust the IMAP source. It often contains wrong encodings that will crash the
                //sync. eg. incredimail.
                $message = new Swift_Message();
                $message->setTo($msg->to);
                $message->setFrom(go()->getSettings()->systemEmail,  go()->getSettings()->title);
                $message->setSubject($subject);
                $message->setDate(new DateTime());
                $message->setBody($this->getHtmlBody(), 'text/html');
                $asBodyData = $message->toString();
                break;
            case SYNC_BODYPREFERENCE_RTF:
                ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->GetMessage RTF Format NOT CHECKED");
                $asBodyData = base64_encode(\GO\Base\Util\StringHelper::normalizeCrlf($this->getHtmlBody() ));
                break;
        }

		$msg->asbody = new SyncBaseBody();
		$msg->asbody->type = $bpReturnType;
		$msg->asbody->truncated = 0;
		$msg->asbody->data = StringStreamWrapper::Open($asBodyData);
		$msg->asbody->estimatedDataSize = strlen($asBodyData);

		return $msg;
	}

    private function getHtmlBody() {
        $asBodyData = nl2br(go()->t('2fa-body', 'legacy', 'sync'));
        return str_replace(['{URL}','{TITLE}'], [
            '<a href="' . go()->getSettings()->URL .'">' . go()->getSettings()->URL .'</a>',
            go()->getSettings()->title],
            $asBodyData);
    }

	public function GetFolder($id)
	{
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("StubProvider->GetFolder('%s')", $id));

        $folder = new SyncFolder();
        $folder->serverid = $id;
        $folder->parentid = "0";
        $folder->displayname = "Inbox";
        $folder->type = SYNC_FOLDER_TYPE_INBOX;

        return $folder;

	}

    public function SetReadFlag($folderid, $id, $flags, $contentparameters) {
        return true;
    }

	public function StatMessage($folderid, $id)
	{
		return [
			'id' => $id,
			'flags' => false,
            'star'=> true,
			'mod' => self::ModifiedDate
		];
	}

	public function GetMessageList($folderid, $cutoffdate)
	{
		ZLog::Write(LOGLEVEL_DEBUG, "StubProvider::GetMessageList($folderid, $cutoffdate)");
		return [
			$this->StatMessage($folderid, '**INCOMPLETE-2FA**')
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
		return $this->StatMessage($folderid, $id);
	}

	public function DeleteMessage($folderid, $id, $contentParameters){
		return false; // message is read-only
	}
    public function getNotification($folder=null) {
        return "stub";
    }
}