<?php


namespace GO\Email\Controller;


class FolderController extends \GO\Base\Controller\AbstractController {
	
//	protected $view = 'json';
	
	protected function actionCreate($params){
		
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
				
		$mailbox = new \GO\Email\Model\ImapMailbox($account, array("name"=>$params["parent"]));
		$response['success'] = $mailbox->createChild($params["name"]);
		
		if(!$response['success'])
		{
			$response['feedback']=$account->openImapConnection()->last_error();
		}
		
		return $response;
	}
	
	protected function actionRename($params){
		
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
				
		$mailbox = new \GO\Email\Model\ImapMailbox($account, array("name"=>$params["mailbox"]));
		$response['success'] = $mailbox->rename($params["name"]);
		
		if(!$response['success'])
			$response['feedback']="Failed to rename ".$params['mailbox']." to ".$params['name']."<br /><br />".$account->getImapConnection()->last_error();
		
		
		return $response;
	}
	
	protected function actionSubscribe($params){
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
			$response = array();
			
			
			$rawMail = json_decode($params["mailboxs"]);
			if(is_array($rawMail)) {
				$mailboxs = $rawMail;
			} else {
				$mailboxs = array($rawMail);
			}
	
		foreach ($mailboxs as $mailboxName) {
			
			$mailbox = new \GO\Email\Model\ImapMailbox($account, array("name"=>$mailboxName));
			$mailbox->subscribe();
			
//			$response['success'] = $mailbox->subscribe();
			
//			if(!$mailbox->subscribe()) {
//				$response['success'] = false;
//				
//				break;
//			}
			$response['success'] = true;
		}
		
		if(!$response['success'])
			$response['feedback']="Failed to subscribe to ".$params['mailbox'];
//		return $response;
		
		echo $this->render('json', array('success' => true));
	}
	
	protected function actionUnsubscribe($params){
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
				
		$response = array();
		$rawMail = json_decode($params["mailboxs"]);
		
			if(is_array($rawMail)) {
				$mailboxs = $rawMail;
			} else {
				$mailboxs = array($rawMail);
			}
			
		foreach ($mailboxs as $mailboxName) {
			
			$mailbox = new \GO\Email\Model\ImapMailbox($account, array("name"=>$mailboxName));
			
			
			if(!$mailbox->unsubscribe()) {
				$response['success'] = false;
				break;
			}
			$response['success'] = true;
		}
		
		if(!$response['success'])
			$response['feedback']="Failed to unsubscribe from ".$params['mailbox'];
		
		return $response;
	}
	
	protected function actionDelete($params){
		$msg = '';
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);

		$mailbox = new \GO\Email\Model\ImapMailbox($account, array("name"=>$params["mailbox"]));
		if($mailbox->isSpecial()) {
			throw new \Exception(\GO::t("You can't delete the trash, sent items or drafts folder", "email"));
		}
		if(array_key_exists('trashable', $params) && intval($params['trashable']) === 0) {
			// The 'Trash' mailbox has the flag 'noinferiors' enabled. The end user was warned, we can safely delete
			$success = $mailbox->delete();
		} elseif(!empty($account->trash) && strpos($params['mailbox'],$account->trash) !== 0) {
			$targetMailbox = new \GO\Email\Model\ImapMailbox($account, ["name" => $account->trash]);

			if($targetMailbox->getHasChildren()) {
				
				if($counter = $this->getCounterMailboxName($targetMailbox, $mailbox->getBaseName())) {
					$mailbox->rename($mailbox->getBaseName() . $counter);
				}
			}
			$success = $mailbox->move($targetMailbox);
		} else {
			$success = $mailbox->delete();
		}
		if(!$success) {
			$msg = go()->t("Failed to delete folder") . ": " . $account->getImapConnection()->last_error();
		}
		return ['success' => $success, 'feedback' => $msg];
	}
	
	private function getCounterMailboxName ($mailbox, $name, $counter = 0) {
		foreach ($mailbox->getChildren() as $childMailbox) {
			if($counter) {
				$chackName = $name.$counter;
			} else {
				$chackName = $name;
			}
			
			if($childMailbox->getBaseName() == $chackName){
				return $this->getCounterMailboxName ($mailbox, $name, $counter+1);
			}
			
		}
		return $counter;
	}


	protected function actionTruncate($params){
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);

		$mailbox = new \GO\Email\Model\ImapMailbox($account, array("name" => $params["mailbox"]));

		if (!empty($account->trash) && $params["mailbox"] != $account->trash) {
			$imap = $account->openImapConnection($params["mailbox"]);
			$uids = $imap->sort_mailbox();
			$imap->set_message_flag($uids, "\Seen");
			$success = $imap->move($uids, $account->trash);
		} else {
			$success = $mailbox->truncate();
		}

		return ["success" => $success];
	}
	
	protected function actionMarkAsRead($params){
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
				
		/* @var $imap \GO\Base\Mail\Imap */	
		
		$imap = $account->openImapConnection($params["mailbox"]);
		$uids = $imap->search("unseen");
		$success=$imap->set_message_flag($uids, "\Seen");
		
		return array("success"=>$success);
	}
	
	protected function actionMove($params){
		
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
				
		$sourceMailbox = new \GO\Email\Model\ImapMailbox($account, array("name"=>$params["sourceMailbox"]));
		
		if($sourceMailbox->isSpecial())
			throw new \Exception(\GO::t("You can't move the trash, sent items or drafts folder", "email"));
		
		$targetMailbox = new \GO\Email\Model\ImapMailbox($account, array("name"=>$params["targetMailbox"]));
			
		
		$response['success'] = $sourceMailbox->move($targetMailbox);
		if(!$response['success'])
			$response['feedback']="Could not move folder $sourceMailbox to $targetMailbox.<br /><br />".$account->getImapConnection()->last_error();
		
		
		return $response;
	}

	/**
	 * @param array $params
	 * @return array
	 * @throws \GO\Base\Exception\AccessDenied
	 * @throws \GO\Base\Mail\Exception\ImapAuthenticationFailedException
	 * @throws \GO\Base\Mail\Exception\MailboxNotFound
	 */
	protected function actionStore(array $params): array
	{

		\GO::session()->closeWriting();

		$response = array(
			"results" => array(),
			"success" => true
		);

		// Dirty hack. OAuth2 accounts that are not fully configured should not retrieve mailboxes
		$bfetchMailboxes = true;
		if(go()->getModule('community', 'oauth2client')) {
			$acct = \go\modules\community\email\model\EmailAccount::findById($params['account_id']);
			$clt = $acct->oauth2_account;
			if($clt && ! $clt->token) {
				$bfetchMailboxes = false;
			}
		}
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
		if($bfetchMailboxes) {
			$mailboxes = $account->getAllMailboxes(false, false);
			foreach ($mailboxes as $mailbox) {
				$response['results'][] = array('name' => $mailbox->name, 'account_id' => $params['account_id']);
			}
		}

		$response['trash'] = $account->trash;

		return $response;
	}
	
	
	protected function actionAclStore($params) {
		
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
		$imap = $account->openImapConnection($params['mailbox']);
		
		if (isset($params['delete_keys'])) {
			try {
				$response['deleteSuccess'] = true;
				$delete_ids = json_decode($params['delete_keys']);
				foreach ($delete_ids as $id) {
					$imap->delete_acl($params['mailbox'], $id);
				}
			} catch (\Exception $e) {
				$response['deleteSuccess'] = false;
				$response['deleteFeedback'] = $e->getMessage();
			}
		}

		$response['success']=true;
		$response['results'] = $imap->get_acl($params['mailbox']);

		foreach ($response['results'] as &$record) {
			$record['read'] = strpos($record['permissions'], 'r') !== false;
			$record['write'] = strpos($record['permissions'], 'w') !== false;
			$record['delete'] = strpos($record['permissions'], 't') !== false;
			$record['createmailbox'] = strpos($record['permissions'], 'k') !== false;
			$record['deletemailbox'] = strpos($record['permissions'], 'x') !== false;
			$record['admin'] = strpos($record['permissions'], 'a') !== false;
		}
		
		return $response;
	}
	
	protected function actionSetAcl($params) {
		$account = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
		$imap = $account->openImapConnection($params['mailbox']);

		$perms = '';

		//lrwstipekxacd

		if (isset($params['read'])) 
			$perms .='lrs';		

		if (isset($params['write'])) 
			$perms .='wip';		

		if (isset($params['delete'])) 
			$perms .='te';		

		if (isset($params['createmailbox'])) 
			$perms .='k';
		
		if (isset($params['deletemailbox'])) 
			$perms .='x';
		
		if (isset($params['admin'])) 
			$perms .='a';
		

		$response['success'] = $imap->set_acl($params['mailbox'], $params['identifier'], $perms);
		
		if(!$response['success'])
			$response['feedback']=$imap->last_error();
		return $response;
	}
	
	protected function actionLoad($params) {
		$response = array( 'success'=>true, 'data'=>array() );
		
		$mailboxPath = $params['mailboxPath'];
		$accountId = $params['accountId'];
		
		$accountModel = \GO\Email\Model\Account::model()->findByPk($accountId);
		
		$checkUnseenMailboxArray = explode(',',$accountModel->check_mailboxes);
		
		$response['data']['checkUnseen'] = in_array( $mailboxPath, $checkUnseenMailboxArray );
		$response['data']['accountId'] = $accountId;
		$response['data']['mailboxPath'] = $mailboxPath;
		
		return $response;
	}
	
	protected function actionSubmit($params) {
		$response = array( 'success' => true, 'id' => $params['accountId'] );
		
		$mailboxPath = $params['mailboxPath'];
		$accountId = $params['accountId'];
		$checkUnseen = !empty($params['checkUnseen']);
		
		$accountModel = \GO\Email\Model\Account::model()->findByPk($accountId);
		
		$checkUnseenMailboxArray = explode(',',$accountModel->check_mailboxes);
		
		if ($checkUnseen && !in_array($mailboxPath,$checkUnseenMailboxArray)) {
			$checkUnseenMailboxArray[] = $mailboxPath;
			$accountModel->check_mailboxes = implode(',',$checkUnseenMailboxArray);
		} elseif (!$checkUnseen && in_array($mailboxPath,$checkUnseenMailboxArray)) {
			$arr = array();
			foreach ($checkUnseenMailboxArray as $k => $v)
				if ($v!=$mailboxPath)
					$arr[] = $v;
				
			$accountModel->check_mailboxes = implode(',',$arr);
		}
		
		$accountModel->save();
		
		return $response;
	}
	
}
