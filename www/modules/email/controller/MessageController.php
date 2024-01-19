<?php


namespace GO\Email\Controller;

use Exception;
use GO;
use GO\Base\Exception\AccessDenied;
use GO\Base\Exception\NotFound;
use GO\Base\Mail\Imap;
use GO\Base\Mail\Mailer;
use GO\Base\Mail\SmimeMessage;
use GO\Base\Model\Acl;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\fs\FileSystemObject;
use go\core\mail\AddressList;
use go\core\model\Module;
use go\core\model\User;
use GO\Email\Model\Alias;
use GO\Email\Model\Account;
use GO\Email\Model\ImapMessage;
use GO\Email\Model\Label;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\calendar\model\Scheduler;


class MessageController extends \GO\Base\Controller\AbstractController
{

	protected function allowGuests()
	{
		return array("mailto");
	}
	/*
	 * Example URL: http://localhost/groupoffice-4.0/www/?r=email/message/mailto&mailto=mailto:info@intermesh.nl&bcc=test@intermesh.nl&body=jaja&cc=cc@intermesh.nl&subject=subject
	 */
	protected function actionMailto($params)
	{
		$qs=str_replace('mailto:','', urldecode($_SERVER['QUERY_STRING']));
		$qs=str_replace('?subject','&subject', $qs);
		$qs=str_replace('?body','&body', $qs);

		parse_str($qs, $vars);

		$vars['to']=isset($vars['mailto']) ? strtolower($vars['mailto']) : '';
		unset($vars['mailto'], $vars['r']);

		if(!isset($vars['subject'])) {
			$vars['subject'] = '';
		}

		if(!isset($vars['body'])) {
			$vars['body'] = '';
		} else{
			$vars['body'] = nl2br($vars['body']);
		}

		header('Location: '.GO::createExternalUrl('email', 'showComposer', array('values'=>$vars)));
		exit();
	}

	protected function actionNotification(array $params): array
	{
		$account = Account::model()->findByPk($params['account_id']);
		
		$alias = $this->_findAliasFromRecipients($account, new \GO\Base\Mail\EmailRecipients($params['message_to']));	
		if(!$alias) {
			$alias = $account->getDefaultAlias();
		}

		$body = sprintf(GO::t("Your message with subject \"%s\" was displayed at %s", "email"), $params['subject'], \GO\Base\Util\Date::get_timestamp(time()));

		$message = new \GO\Base\Mail\Message(
						sprintf(GO::t("Read: %s", "email"),$params['subject']),
						$body
						);
		$message->setFrom($alias->email, $alias->name);

		$toList = new AddressList($params['notification_to']);

		$message->setTo(...$toList->toArray());

		$mailer = Mailer::newGoInstance();
		$mailer->setEmailAccount($account);
		$response['success'] = $mailer->send($message);

		return $response;
	}


	private function _moveMessages($imap, $params, &$response, $account)
	{
		if(isset($params['action']) && $params['action']=='move') {

			if(!$account->checkPermissionLevel(Acl::CREATE_PERMISSION)){
				throw new \GO\Base\Exception\AccessDenied();
			}
			
			$messages = json_decode($params['messages']);
			$imap->move($messages, $params['to_mailbox']);

			//return possible changed unseen status
			$unseen = $imap->get_unseen($params['to_mailbox']);
			$response['unseen'][$params['to_mailbox']]=$unseen['count'];
		}
	}

	private function _filterMessages($mailbox, Account $account)
	{
		$filters = $account->filters->fetchAll();

		if (count($filters)) {
			$imap = $account->openImapConnection($mailbox);

			$messages = ImapMessage::model()->find($account, $mailbox,0, 100, Imap::SORT_ARRIVAL, false, "UNSEEN");
			if(count($messages)){
				while ($filter = array_shift($filters)) {
					$matches = array();
					$notMatched = array();
					while ($message = array_shift($messages)) {
						if (stripos($message->{$filter->field}, $filter->keyword) !== false) {
							$matches[] = $message->uid;
						} else {
							$notMatched[] = $message;
						}
					}
					$messages = $notMatched;

					if(count($matches)){
						if ($filter->mark_as_read) {
							$imap->set_message_flag($matches, "\Seen");
						}
						$imap->move($matches, $filter->folder);
					}
				}
			}
		}
	}

	protected function actionTestSearch()
	{
		$imapSearch = new \GO\Email\Model\ImapSearchQuery();
		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::SUBJECT);
		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::SUBJECT);

		$imapSearch->searchOld();
		$command = $imapSearch->getImapSearchQuery();

		echo $command."</br>";

		$account = Account::model()->findByPk(145);
		$imap = $account->openImapConnection('INBOX');

		$messages = ImapMessage::model()->find(
						$account,
						'INBOX',
						0, 
						50, 
						Imap::SORT_DATE , 
						'ASC', 
						$command);

		$response["results"]=array();
		foreach($messages as $message){
			$record = $message->getAttributes(true);
			$record['subject'] = htmlspecialchars($record['subject'],ENT_COMPAT,'UTF-8');
			$response["results"][]=$record;
		}

		$response['total'] = $imap->sort_count;

		return $response;
	}

	protected function actionStore(array $params)
	{

		$this->checkRequiredParameters(array('account_id'), $params);

		GO::session()->closeWriting();

		if(!isset($params['start'])) {
			$params['start'] = 0;
		}

		if(!isset($params['limit'])) {
			$params['limit'] = GO::user()->max_rows_list;
		}
		if(!isset($params['dir'])) {
			$params['dir'] = "ASC";
		}

		$query=isset($params['query']) ? $params['query'] : "";

		//passed when only unread should be shown
		if(!empty($params['unread'])) {
			$query = str_replace(array('UNSEEN', 'SEEN'), array('', ''), $query);
			if ($query == '') {
				$query .= 'UNSEEN';
			} else {
				$query .= ' UNSEEN';
			}
		}
		if(!empty($params['flagged'])) {
			$query = str_replace(array('UNFLAGGED', 'FLAGGED'), array('', ''), $query);
			if ($query == '') {
				$query .= 'FLAGGED';
			} else {
				$query .= ' FLAGGED';
			}
		}

		/* @var $account Account */
		$account = Account::model()->findByPk($params['account_id']);
		if(!$account) {
			throw new NotFound();
		}

		$this->_filterMessages($params["mailbox"], $account);

		$imap = $account->openImapConnection($params["mailbox"]);

		$response['permission_level'] = $account->getPermissionLevel();

		// ADDED EXPUNGE SO THE FOLDER WILL BE UP TO DATE (When moving folders in THUNDERBIRD)
		$imap->expunge();
		$response['unseen']=array();

		//special folder flags
		$response['sent']=!empty($account->sent) && strpos($params['mailbox'],$account->sent)===0;
		$response['drafts']=!empty($account->drafts) && strpos($params['mailbox'],$account->drafts)===0;
		$response['trash']=!empty($account->trash) && strpos($params['mailbox'],$account->trash)===0;

		$this->_moveMessages($imap, $params, $response,$account);


		$sort=isset($params['sort']) ? $params['sort'] : 'from';

		switch($sort) {
			case 'from':
				$sortField=$response['sent'] ? Imap::SORT_TO : Imap::SORT_FROM;
				break;
			case 'internal_udate':
			case 'arrival':
				$sortField=Imap::SORT_ARRIVAL; //arrival is faster on older mail servers
				break;


			case 'subject':
				$sortField=Imap::SORT_SUBJECT;
				break;
			case 'size':
				$sortField=Imap::SORT_SIZE;
				break;
			default:
				$sortField=Imap::SORT_DATE;
		}

		if (!empty($params['delete_keys'])) {
			if(!$account->checkPermissionLevel(Acl::CREATE_PERMISSION)) {
				$response['deleteFeedback'] = GO::t("You don't have permission to perform this action");
			}else {
				$uids = json_decode($params['delete_keys']);

				if(!$response['trash'] && !empty($account->trash)) {
					$imap->set_message_flag($uids, "\Seen");
					$response['deleteSuccess']=$imap->move($uids,$account->trash);
				} else {
					$response['deleteSuccess']=$imap->delete($uids);
				}
				if(!$response['deleteSuccess']) {
					$lasterror = $imap->last_error();
					if(stripos($lasterror,'quota')!==false) {
						$response['deleteFeedback']=GO::t("Your mailbox is full. Empty your trash folder first. If it is already empty and your mailbox is still full, you must disable the Trash folder to delete messages from other folders. You can disable it at:

Settings -> Accounts -> Double click account -> Folders.", "email");
					} else {
						$response['deleteFeedback']=GO::t("Error while deleting the data").":\n\n".$lasterror."\n\n".GO::t("Moving the e-mail to the trash folder failed. This might be because you are out of disk space. You can only free up space by disabling the trash folder at Administration -> Accounts -> Double click your account -> Folders", "email");
					}
				}
			}
		}


		//make sure we are connected to the right mailbox after move and delete operations
		$response['multipleFolders']=false;
		$searchIn = 'current'; //default to current if not set
		if(isset($params['searchIn']) && in_array($params['searchIn'], array('all', 'recursive'))) {
			$searchIn = $params['searchIn'];
			$response['multipleFolders'] = true;
		}

		$messages = ImapMessage::model()->find(
						$account,
						$params['mailbox'],
						$params['start'],
						$params['limit'],
						$sortField ,
						$params['dir']!='ASC',
						$query,
						$searchIn);

		$labels = Label::model()->getAccountLabels($account->id);

		$response["results"]=array();
		foreach ($messages as $message) {
			$record = $message->getAttributes(true);
			
			$messageMailbox = new \GO\Email\Model\ImapMailbox($message->account, array('name'=>$message->mailbox));
			$record['mailboxname'] = $messageMailbox->getDisplayName();
			$record['account_id'] = $account->id;

			if(!isset($record['mailbox'])) {
				$record['mailbox'] = $params["mailbox"];
			}
			$record['labels'] = array();
			foreach ($message->labels as $label) {
				if (isset($labels[$label])) {
					$record['labels'][] = array(
						'name' => $labels[$label]->name,
						'color' => $labels[$label]->color,
						'flag' => $labels[$label]->flag
					);
				}
			}
			
			$addresses = $message->to->getAddresses();
			$to=array();
			foreach($addresses as $email=>$personal) {
				$to[]=empty($personal) ? $email : $personal;
			}
			$record['from'] =  htmlspecialchars($record['from'], ENT_COMPAT, 'UTF-8');
			$record['to']=  htmlspecialchars(implode(',', $to), ENT_COMPAT, 'UTF-8');
			
			if ($response['sent'] || $response['drafts']) {
				$to = $record['to'];
				$record['to'] = $record['from'];
				$record['from'] = $to;
			}

			if(empty($record['subject'])) {
				$record['subject'] = GO::t("No subject", "email");
			} else {
				$record['subject'] = htmlspecialchars($record['subject'], ENT_COMPAT, 'UTF-8');
			}
			$response["results"][] = $record;
		}

		$response['total'] = $imap->sort_count;
		
		// Return all UIDs if we have a search query in case we need to perform actions on the entire set
		if (!empty($query)) {
			$response['allUids']=$imap->allUids;
		}

		$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name'=>$params['mailbox']));
		$mailbox->snoozeAlarm();

		$response['unseen'][$params['mailbox']] = $mailbox->unseen;

		//deletes must be confirmed if no trash folder is used or when we are in the trash folder to delete permanently
		$response['deleteConfirm'] = empty($account->trash) || $account->trash==$params['mailbox'];

		return $response;
	}
	


	/**
	 * Add a flag to one or multiple messages
	 *
	 * @param array $params
	 * - int account_id: the id of the GO email account
	 * - string messages: the json encoded mail messages
	 * - string mailbox: the mailbox the find the messages in
	 * - string flag: the flag to set. eg "FLAG"
	 * - boolean clear: true is the other flags should be removed
	 * @return array
	 */
	protected function actionSetFlag(array $params)
	{
		GO::session()->closeWriting();

		$messages = json_decode($params['messages']);

		$account = Account::model()->findByPk($params['account_id']);

		$requiredPermissionLevel = $params["flag"]=='Seen' && !empty($params["clear"]) ? Acl::CREATE_PERMISSION : Account::ACL_DELEGATED_PERMISSION;

		if(!$account->checkPermissionLevel($requiredPermissionLevel)) {
			throw new \GO\Base\Exception\AccessDenied();
		}

		$imap = $account->openImapConnection($params["mailbox"]);

		if (in_array(ucfirst($params['flag']), Imap::$systemFlags)) {
			$params["flag"] = "\\".ucfirst($params["flag"]);
		}

		$response['success']=$imap->set_message_flag($messages, $params["flag"], !empty($params["clear"]));

		$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name'=>$params['mailbox']));
		$mailbox->snoozeAlarm();

		$response['unseen'] = $mailbox->unseen;

		return $response;
	}




	protected function actionSave(array $params)
	{
		GO::session()->closeWriting();

		$alias = Alias::model()->findByPk($params['alias_id']);
		$account = Account::model()->findByPk($alias->account_id);

		if (empty($account->drafts)) {
			throw new \Exception(GO::t("Message could not be saved because the 'Drafts' folder is disabled.\n\nGo to E-mail -> Administration -> Accounts -> Double click account -> Folders to configure it.", "email"));
		}
		$message = new \GO\Base\Mail\Message();

		$message->handleEmailFormInput($params);

		$message->setFrom($alias->email, $alias->name);

		$imap = $account->openImapConnection($account->drafts);

		$nextUid = $imap->get_uidnext();
		$response=array('success'=>false);
		if ($nextUid) {
			$response['sendParams']['draft_uid'] = $nextUid;
			$response['success'] = $response['sendParams']['draft_uid'] > 0;
		}

		if(!$imap->append_message($account->drafts, $message->toString(), "\Seen")){
			$response['success'] = false;
			$response['feedback']=$imap->last_error();
		}

		if (!empty($params['draft_uid'])) {
			//remove older draft version
			$imap = $account->openImapConnection($account->drafts);
			$imap->delete(array($params['draft_uid']));
		}

		if (!$nextUid) {
			$account->drafts = '';
			$account->save();

			$response['feedback'] = GO::t("Your mail server does not support UIDNEXT. The 'Drafts' folder is disabled automatically for this account now.", "email");
		}

		return $response;
	}


	protected function actionSaveToFile(array $params)
	{
		$message = new \GO\Base\Mail\Message();
		$alias = Alias::model()->findByPk($params['alias_id']);
		$message->handleEmailFormInput($params);
		$message->setFrom($alias->email, $alias->name);

		$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.$params['save_to_path']);

		$file->putContents($message->toStream());

		$response['success']=$file->exists();

		return $response;
	}

	private function _createAutoLinkTagFromParams(array $params, $account)
	{
		$tag = '';
		if (!empty($params['links'])) {
			$links = json_decode($params['links'], true);
			
			foreach($links as $link) {			
				$tag .= $this->_createAutoLinkTag($account,$link['toEntity'],$link['toId']);
			}
		}
		return $tag;
	}


	private function _createAutoLinkTag($account, $model_name, $model_id)
	{
		return "[link:".base64_encode($_SERVER['SERVER_NAME'].','.$account->id.','.$model_name.','.$model_id)."]";
	}

	private function _findUnknownRecipients(array $params)
	{
		$unknown = array();

		if (GO::modules()->addressbook && !GO::config()->get_setting('email_skip_unknown_recipients', GO::user()->id)) {

			$recipients = new \GO\Base\Mail\EmailRecipients($params['to']);
			$recipients->addString($params['cc']);
			$recipients->addString($params['bcc']);

			foreach ($recipients->getAddresses() as $email => $personal) {
				$contact = \go\modules\community\addressbook\model\Contact::findByEmail($email, ['id', 'addressBookId'])
		          ->filter(["permissionLevel" => \go\core\model\Acl::LEVEL_READ])->single();
				if($contact) {
					continue;
                }

				$user = User::find(['id'])->where(['email' => $email])->filter(["permissionLevel" => \go\core\model\Acl::LEVEL_READ])->single();
		        if($user) {
					continue;
		        }

				$recipient = \GO\Base\Util\StringHelper::split_name($personal);
				if ($recipient['first_name'] == '' && $recipient['last_name'] == '') {
					$recipient['first_name'] = $email;
				}
				$recipient['email'] = $email;
				$recipient['name'] = (string) \GO\Base\Mail\EmailRecipients::createSingle($email, $personal);

				$unknown[] = $recipient;
			}
		}

		return $unknown;
	}


	/**
	 *
	 * @todo Save to sent items should be implemented as a Swift outputstream for better memory management
	 * @param array $params
	 * @return array
	 * @throws Exception
	 */
	protected function actionSend(array $params)
	{
		GO::session()->closeWriting();

		$response['success'] = true;
		$response['feedback']='';

		$alias = Alias::model()->findByPk($params['alias_id']);
		$account = Account::model()->findByPk($alias->account_id);

		$message = new SmimeMessage();

		// add tags in new mail for linking later
		$tag = $this->_createAutoLinkTagFromParams($params, $account);

		if(!empty($tag)){
			if($params['content_type']=='html') {
				$params['htmlbody'] .= '<div style="width:1px;height:1px;padding-left:1px;overflow:hidden">' . $tag . '</div>';
			} else {
				$params['plainbody'] .= "\n\n" . $tag . "\n\n";
			}
		}

		// insert params into new SmimeMessage
		$message->handleEmailFormInput($params);
		$recipientCount = $message->countRecipients();
		if(!$recipientCount) {
			throw new \Exception(GO::t("You didn't enter a recipient", "email"));
		}
		$message->setFrom($alias->email, $alias->name);
		
		$mailer = Mailer::newGoInstance();
		$mailer->setEmailAccount($account);


		$this->fireEvent('beforesend', array(
				&$this,
				&$response,
				&$message,
				&$mailer,
				$account,
				$alias,
				$params
		));

		
		$success = $mailer->send($message);

		if(!$success) {
			$msg = GO::t("Sorry, an error occurred") . ': '. $mailer->lastError();
			throw new Exception($msg);
		}

		// Update "last mailed" time of the emailed contacts.
		if ($success && GO::modules()->addressbook) {
			$toAddresses = $message->getTo();
			if (empty($toAddresses)) {
				$toAddresses = array();
			}
			$ccAddresses = $message->getCc();
			if (empty($ccAddresses)) {
				$ccAddresses = array();
			}
			$bccAddresses = $message->getBcc();
			if (empty($bccAddresses)) {
				$bccAddresses = array();
			}
			$emailAddresses = array_merge($toAddresses,$ccAddresses);
			$emailAddresses = array_merge($emailAddresses,$bccAddresses);
			$emailAddresses = array_keys($emailAddresses);

			$contacts = Contact::findByEmail($emailAddresses)->filter(['permissionLevel' => Acl::READ_PERMISSION])->selectSingleValue('c.id');
			foreach($contacts as $contactId) {
				go()->getDbConnection()->replace(
					'em_contacts_last_mail_times',
					[
						'contact_id' => $contactId,
						'user_id' => go()->getAuthState()->getUserId(),
						'last_mail_time' => time()
					])->execute();
			}
		}

		if (!empty($params['reply_uid'])) {
			//set \Answered flag on IMAP message
			GO::debug("Reply");
			$account2 = Account::model()->findByPk($params['reply_account_id']);
			$imap = $account2->openImapConnection($params['reply_mailbox']);
			$imap->set_message_flag(array($params['reply_uid']), "\Answered");
		}

		if (!empty($params['forward_uid'])) {
			//set forwarded flag on IMAP message
			$account2 = Account::model()->findByPk($params['forward_account_id']);
			$imap = $account2->openImapConnection($params['forward_mailbox']);
			$imap->set_message_flag(array($params['forward_uid']), "\$Forwarded");
		}

		/**
		 * if you want ignore default sent folder message will be store in
		 * folder wherefrom user sent it
		 */
		if ($account->ignore_sent_folder && !empty($params['reply_mailbox'])) {
			$account->sent = $params['reply_mailbox'];
		}

		if ($success) {
			//if a sent items folder is set in the account then save it to the imap folder
			// auto linking will happen on save to sent items
			if(!$account->saveToSentItems($message, $params)){
				//$imap->append_message($account->sent, $message, "\Seen");
				$response['success']=false;
				$response['feedback'].='Failed to save sent item to '.$account->sent;
			}
		}		

		if (!empty($params['draft_uid'])) {
			//remove drafts on send
			$imap = $account->openImapConnection($account->drafts);
			$imap->delete(array($params['draft_uid']));
		}
		


		$response['unknown_recipients'] = $this->_findUnknownRecipients($params);

		return $response;
	}

	private function _addEmailsAsAttachment($message, array $params)
	{
		if(!empty($params['addEmailAsAttachmentList'])) {
			$addEmailAsAttachmentList = json_decode($params['addEmailAsAttachmentList']);
			$account = Account::model()->findByPk($params['account_id']);
			$numberAttachment = 1;
			foreach ($addEmailAsAttachmentList as $value) {
				$attachmentMessage = ImapMessage::model()->findByUid($account, $value->mailbox, $value->uid);

				$filename = GO\Base\Fs\File::stripInvalidChars($attachmentMessage->subject);

				$filename .= ".eml";
				$tempDir = $this->getTempDir($params['account_id'], $value->mailbox, $value->uid);
				$tmpFile = new \GO\Base\Fs\File($tempDir . $filename);
				$tmpFile->putContents($attachmentMessage->getSource());

				$MessageAttachment = GO\Email\Model\MessageAttachment::model()->createFromTempFile($tmpFile);
				$MessageAttachment->number = $numberAttachment++;

				$message->addAttachment($MessageAttachment);
				GO::debug($numberAttachment);
			}
		}
	}
	
	public function loadTemplate(array $params)
	{
		$unsetSubject = true;
		
		if (!empty($params['template_id'])) {
			try {
				$template = \GO\Base\Model\Template::model()->findByPk($params['template_id']);
				$templateContent = $template ? $template->content : '';
			} catch (\GO\Base\Exception\AccessDenied $e) {
				$templateContent = "";
			}
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($templateContent, false);
			
			$unsetSubject = empty($message->subject);
			
			$this->_setAddressFields($params, $message);
			$this->_addEmailsAsAttachment($message,$params);
			
			$response['data'] = $message->toOutputArray(true, true);
			
			if(!empty($params['subject'])) {
				$unsetSubject = false;
				$response['data']['subject'] = $params['subject'];
			}

			$presetbody = isset($params['body']) ? $params['body'] : '';
			if (!empty($presetbody) && strpos($response['data']['htmlbody'], '{body}') == false) {
				$response['data']['htmlbody'] = $params['body'] . '<br />' . $response['data']['htmlbody'];
			} else {
				$response['data']['htmlbody'] = str_replace('{body}', $presetbody, $response['data']['htmlbody']);
			}

			$defaultTags = array(
				'contact:salutation' => GO::t("Dear sir/madam")
			);
			
			// Parse the link tag
			$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceLinkTag($response['data']['htmlbody'], $message);
			
			//keep template tags for mailings to addresslists
			if (empty($params['addresslist_id'])) {
				//if contact_id is not set but email is check if there's contact info available
				if (!empty($params['to']) || !empty($params['contact_id']) || !empty($params['company_id'])) {
					if (!empty($params['contact_id'])) {
						$contact = \go\modules\community\addressbook\model\Contact::findById($params['contact_id']);
					} else {
						$email = \GO\Base\Util\StringHelper::get_email_from_string($params['to']);		
						
						$contact = \go\modules\community\addressbook\model\Contact::find()
							->filter(['email' => $email, 'permissionLevel' => \go\core\model\Acl::LEVEL_READ])
							->single();
					}

					if ($contact) {
						$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceContactTags($response['data']['htmlbody'], $contact, true);
					} else {
						$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceCustomTags($response['data']['htmlbody'],$defaultTags, true);
						$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceUserTags($response['data']['htmlbody'], true);
					}
				} else {
					$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceCustomTags($response['data']['htmlbody'],$defaultTags, true);
					$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceUserTags($response['data']['htmlbody'],true);
				}
				
				if(!empty($params['alias_id']) && ($alias = Alias::model()->findByPk($params['alias_id']))) {
					$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceModelTags($response['data']['htmlbody'], $alias, 'alias:', true);
				}
				//cleanup empty tags
				$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceCustomTags($response['data']['htmlbody'],['body' => $params['body'] ?? ""], false);
			}

			if ($params['content_type'] == 'plain') {
				$response['data']['plainbody'] = \GO\Base\Util\StringHelper::html_to_text($response['data']['htmlbody'], false);
				unset($response['data']['htmlbody']);
			}
		} else {
			$message = new \GO\Email\Model\ComposerMessage();

			if(!empty($params['subject'])) {
				$message->subject = $params['subject'];
			}

			$this->_setAddressFields($params, $message);
			$this->_addEmailsAsAttachment($message,$params);
			
			$response['data'] = $message->toOutputArray($params['content_type'] == 'html', true);

			if(isset($params['body'])) {
				if ($params['content_type'] == 'plain') {
					$response['data']['plainbody'] = $params['body'] . "\n" . $response['data']['plainbody'];
				} else {
					$response['data']['htmlbody'] = $params['body'] . '<br />' . $response['data']['htmlbody'];
				}
			}
		}
		
		$this->_keepHeaders($response, $params, $unsetSubject);
		$response['success'] = true;

		return $response;
	}
	
	/**
	 * Set the to, cc and bcc fields if the params are given
	 * 
	 * @param array $params
	 * @param Message $message
	 */
	private function _setAddressFields(array $params, $message)
	{
		if(!empty($params['to'])){			
			$message->to = new \GO\Base\Mail\EmailRecipients($params['to']);		
		}
		if(!empty($params['cc'])){
			$message->cc = new \GO\Base\Mail\EmailRecipients($params['cc']);
		}
		if(!empty($params['bcc'])){
			$message->bcc = new \GO\Base\Mail\EmailRecipients($params['bcc']);
		}
	}
	
	private function getTempDir($accountId, $mailbox, $uid)
	{
		$this->_tmpDir=\GO::config()->tmpdir.'imap_messages/'.$accountId.'-'.$mailbox.'-'.$uid.'/';
		if(!is_dir($this->_tmpDir))
			mkdir($this->_tmpDir, 0700, true);
		return $this->_tmpDir;
	}
	
	/**
	 * When changing content type or template in email composer we don't want to
	 * reset some header fields.
	 *
	 * @param array $response
	 * @param array $params
	 * @param bool $unsetSubject
	 *
	 */
	private function _keepHeaders(array &$response, array $params, bool $unsetSubject = true)
	{
		if (!empty($params['keepHeaders'])) {
			unset(
				$response['data']['alias_id'],
				$response['data']['to'],
				$response['data']['cc'],
				$response['data']['bcc']
			);
			
			if($unsetSubject) {
				unset($response['data']['subject']);
			}
		}
	}

	protected function actionTemplate(array $params)
	{
		$response = $this->loadTemplate($params);
		return $response;
	}

	private function _quoteHtml(string $html)
	{
		return '<blockquote style="border:0;border-left: 2px solid #22437f; padding:0px; margin:0px; padding-left:5px; margin-left: 5px; ">' .
						$html .
						'</blockquote>';
	}

	private function _quoteText(string $text) {
		$text = \GO\Base\Util\StringHelper::normalizeCrlf($text, "\n");

		return '> ' . str_replace("\n", "\n> ", $text);
	}

	protected function actionOpenDraft(array $params)
	{
		if (!empty($params['uid'])) {
			$account = Account::model()->findByPk($params['account_id']);
			$message = ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
			$message->createTempFilesForAttachments();
			$response['sendParams']['draft_uid'] = $message->uid;
		} else {
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeFile($params['path']);
		}
		$response['data'] = $message->toOutputArray($params['content_type'] == 'html', true,false,false);

		if (!empty($params['uid'])) {
			$alias = $this->_findAliasFromRecipients($account, $message->from,0,true);	
			
			if($alias) {
				$response['data']['alias_id'] = $alias->id;
			}
		}

		$response['success'] = true;
		return $response;
	}

	/**
	 * Reply to a mail message. It can handle an IMAP message or a saved message.
	 *
	 * @param array $params
	 * @throw NotFound()
	 */
	protected function actionReply(array $params)
	{
		if(!empty($params['uid'])){
			$account = Account::model()->findByPk($params['account_id']);
			if(!$account) {
				throw new NotFound();
			}

			$message = ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
			if(!$message) {
				throw new NotFound();
			}
		} else {
			$account=false;
			$message =  \GO\Email\Model\SavedMessage::model()->createFromMimeFile($params['path'], !empty($params['is_tmp_file']) && $params['is_tmp_file']!='false');
		}

		return $this->_messageToReplyResponse($params, $message, $account);
	}

	private function _messageToReplyResponse(array $params, \GO\Email\Model\ComposerMessage $message, $account=false)
	{
		$html = $params['content_type'] == 'html';

		$fullDays = GO::t("full_days");
		
		$replyTo = $message->reply_to->count() ? $message->reply_to : $message->from;
		
		if(!isset($params['alias_id'])) {
			$params['alias_id'] = 0;
		}
		
		$recipients = new \GO\Base\Mail\EmailRecipients();
		$recipients->mergeWith($message->cc)->mergeWith($message->to);
		
		$alias = $this->_findAliasFromRecipients($account, $recipients, $params['alias_id']);	
		
		if (empty($params['account_id']) || $alias->account_id != $params['account_id']) {
			$templateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($alias->account->id);
			if (!$templateModel) {
				$templateModel = \GO\Email\Model\DefaultTemplate::model()->findByPk(\GO::user()->id);
			}

			if($templateModel) {
				$params['template_id'] = $templateModel->template_id;
			}
		}
		$from =$replyTo->getAddress();
		$fromArr = $message->from->getAddress();
		
		//for template loading so we can fill the template tags
		$params['to'] = $from['email'];

		$response = $this->loadTemplate($params);

		$response['data']['template_id'] = $params['template_id'];	
		$response['data']['account_id'] = $alias->account_id;	
		$response['data']['alias_id'] = $alias->id;	

		if ($html) {
			//saved messages always create temp files
			if($message instanceof ImapMessage) {
				$message->createTempFilesForAttachments(true);
			}

			$oldMessage = $message->toOutputArray(true,false,true);
			
			if(!empty($oldMessage['smime_encrypted'])) {
				$response['sendParams']['encrypt_smime'] = true;
			}
			$AccountModel =  Account::model()->findByPk($params['account_id']);
			if($AccountModel->full_reply_headers) {
				$headerLines = $this->_getFollowUpHeaders($message);
				$header = '<br /><br />' . GO::t("--- Original message follows ---", "email") . '<br />';
				foreach ($headerLines as $line) {
					$header .= '<b>' . $line[0] . ':&nbsp;</b>' . htmlspecialchars($line[1], ENT_QUOTES, 'UTF-8') . "<br />";
				}

				$header .= "<br /><br />";
				
				$replyText = $header;
			} else {
				$replyText = sprintf(GO::t("On %s, %s at %s %s wrote:", "email"), $fullDays[date('w', $message->udate)], date(GO::user()->completeDateFormat, $message->udate), date(GO::user()->time_format, $message->udate), $fromArr['personal']);
			}
			
			$response['data']['htmlbody'] .= '<br /><br />' .
								$replyText. //htmlspecialchars($replyText, ENT_QUOTES, 'UTF-8') .
								'<br />' . $this->_quoteHtml($oldMessage['htmlbody']);

			// Fix for array_merge function on line below when the $response['data']['inlineAttachments'] do not exist
			if(empty($response['data']['inlineAttachments'])) {
				$response['data']['inlineAttachments'] = array();
			}

			$response['data']['inlineAttachments'] = array_merge($response['data']['inlineAttachments'], $oldMessage['inlineAttachments']);
		} else {
			$AccountModel =  Account::model()->findByPk($params['account_id']);
			if($AccountModel->full_reply_headers) {
				$headerLines = $this->_getFollowUpHeaders($message);
				$replyText = "\n\n" . GO::t("--- Original message follows ---", "email") . "\n";
				foreach ($headerLines as $line) {
					$replyText .= $line[0] . ': ' . $line[1] . "\n";
				}
				$replyText .= "\n\n";
			} else {
				$replyText = sprintf(GO::t("On %s, %s at %s %s wrote:", "email"), $fullDays[date('w', $message->udate)], date(GO::user()->completeDateFormat, $message->udate), date(GO::user()->time_format, $message->udate), $fromArr['personal']);
			}
			
			$oldMessage = $message->toOutputArray(false,false,true);

			if (!empty($oldMessage['smime_encrypted'])) {
				$response['sendParams']['encrypt_smime'] = true;
			}
			
			$response['data']['plainbody'] .= "\n\n" . $replyText . "\n" . $this->_quoteText($oldMessage['plainbody']);
		}

		if (stripos($message->subject, 'Re:') === false) {
			$response['data']['subject'] = 'Re: ' . $message->subject;
		} else {
			$response['data']['subject'] = $message->subject;
		}
		
		if(isset($params['includeAttachments'])){
			// Include attachments

			if($message instanceof ImapMessage){
				//saved messages always create temp files
				$message->createTempFilesForAttachments();
			}

			$oldMessage = $message->toOutputArray($html,false,true);

			// Fix for array_merge functions on lines below when the $response['data']['inlineAttachments'] and $response['data']['attachments'] do not exist
			if(empty($response['data']['inlineAttachments'])) {
				$response['data']['inlineAttachments'] = array();
			}

			if(empty($response['data']['attachments'])) {
				$response['data']['attachments'] = array();
			}

			$response['data']['inlineAttachments'] = array_merge($response['data']['inlineAttachments'], $oldMessage['inlineAttachments']);
			$response['data']['attachments'] = array_merge($response['data']['attachments'], $oldMessage['attachments']);
		}

		if (empty($params['keepHeaders'])) {
			if (!empty($params['replyAll'])) {
				$toList = new \GO\Base\Mail\EmailRecipients();
				$toList->mergeWith($replyTo)->mergeWith($message->to);

				//remove our own alias from the recipients.		
				if ($toList->count()>1) {
					$toList->removeRecipient($alias->email);
					$message->cc->removeRecipient($alias->email);
				}

				$response['data']['to'] = (string) $toList;
				$response['data']['cc'] = (string) $message->cc;
			} else {
				$response['data']['to'] = (string) $replyTo;
			}
		}

		//for saving sent items in actionSend
		if ($message instanceof ImapMessage) {
			$response['sendParams']['reply_uid'] = $message->uid;
			$response['sendParams']['reply_mailbox'] = $params['mailbox'];
			$response['sendParams']['reply_account_id'] = $params['account_id'];
			$response['sendParams']['in_reply_to'] = $message->message_id;

			//We need to link the contact if a manual link was made of the message to the sender.
			//Otherwise the new sent message may not be linked if an autolink tag is not present.
			if (false && GO::modules()->savemailas) {
				$from = $message->from->getAddress();

				$contact = \GO\Addressbook\Model\Contact::model()->findSingleByEmail($from['email'], \GO\Base\Db\FindParams::newInstance()->permissionLevel(Acl::WRITE_PERMISSION));
				if ($contact) {
					$linkedMessage = \GO\Savemailas\Model\LinkedEmail::model()->findByImapMessage($message, $contact);
					if ($linkedMessage && $linkedMessage->linkExists($contact)){
						$tag = $this->_createAutoLinkTag($account, "GO\Addressbook\Model\Contact", $contact->id);

						if ($html) {
							if(strpos($response['data']['htmlbody'], $tag)===false){
								$response['data']['htmlbody'].= '<div style="display:none">'.$tag.'</div>';
							}
						} else {
							if(strpos($response['data']['plainbody'], $tag)===false){
								$response['data']['plainbody'].= "\n\n".$tag."\n\n";
							}
						}
					}
				}
			}
		}

		$this->_keepHeaders($response, $params);

		return $response;
	}

	/**
	 *
	 * @param Account $account
	 * @param \GO\Base\Mail\EmailRecipients $recipients
	 * @return Alias|false
	 */
	private function _findAliasFromRecipients($account, \GO\Base\Mail\EmailRecipients $recipients, $alias_id=0, $allAvailableAliases=false)
	{
		$alias=false;
		$defaultAlias=false;

		$findParams = \GO\Base\Db\FindParams::newInstance()
				->select('t.*')
				->joinModel(array(
						'model' => 'GO\Email\Model\AccountSort',
						'foreignField' => 'account_id', //defaults to primary key of the remote model
						'localField' => 'account_id', //defaults to primary key of the model
						'type' => 'LEFT'
				))
				->permissionLevel(Acl::CREATE_PERMISSION)
				->ignoreAdminGroup()
				->order('order', 'DESC');


		//find the right sender alias
		$stmt = !$allAvailableAliases && $account && $account->checkPermissionLevel(Acl::CREATE_PERMISSION) ? $account->aliases : Alias::model()->find($findParams);
		while($possibleAlias = $stmt->fetch()){
			if(!$defaultAlias) {
				$defaultAlias = $possibleAlias;
			}

			if($recipients->hasRecipient($possibleAlias->email)){
				$alias = $possibleAlias;
				break;
			}
		}

		if(!$alias) {
			$alias = empty($alias_id) ? $defaultAlias : Alias::model()->findByPk($alias_id);
		}
		return $alias;
	}
	
	/**
	 * Forward a mail message. It can handle an IMAP message or a saved message.
	 *
	 * @param array $params
	 */
	protected function actionForward(array $params)
	{
		if (!empty($params['uid'])) {
			$account = Account::model()->findByPk($params['account_id']);
			$message = ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
		} else {
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeFile($params['path'], !empty($params['is_tmp_file']) && $params['is_tmp_file']!='false');
		}

		return $this->_messageToForwardResponse($params, $message);
	}

	private function _messageToForwardResponse(array $params, \GO\Email\Model\ComposerMessage $message)
	{
		$response = $this->loadTemplate($params);

		$html = $params['content_type'] == 'html';

		if (stripos($message->subject, 'Fwd:') === false) {
			$response['data']['subject'] = 'Fwd: ' . $message->subject;
		} else {
			$response['data']['subject'] = $message->subject;
		}

		$headerLines = $this->_getFollowUpHeaders($message);

		if($message instanceof ImapMessage){
			//saved messages always create temp files
			$message->createTempFilesForAttachments();
		}

		$oldMessage = $message->toOutputArray($html,false,true);

		if(!empty($oldMessage['smime_encrypted'])) {
			$response['sendParams']['encrypt_smime'] = true;
		}

		// Fix for array_merge functions on lines below when the $response['data']['inlineAttachments'] and $response['data']['attachments'] do not exist
		if(empty($response['data']['inlineAttachments'])) {
			$response['data']['inlineAttachments'] = array();
		}
		if(empty($response['data']['attachments'])) {
			$response['data']['attachments'] = array();
		}
		$response['data']['inlineAttachments'] = array_merge($response['data']['inlineAttachments'], $oldMessage['inlineAttachments']);
		$response['data']['attachments'] = array_merge($response['data']['attachments'], $oldMessage['attachments']);

		if ($html) {
			$header = '<br /><br />' . GO::t("--- Original message follows ---", "email") . '<br />';
			foreach ($headerLines as $line) {
				$header .= '<b>' . $line[0] . ':&nbsp;</b>' . htmlspecialchars($line[1], ENT_QUOTES, 'UTF-8') . "<br />";
			}
			$header .= "<br /><br />";

			$response['data']['htmlbody'] .= $header . $oldMessage['htmlbody'];
		} else {
			$header = "\n\n" . GO::t("--- Original message follows ---", "email") . "\n";
			foreach ($headerLines as $line) {
				$header .= $line[0] . ': ' . $line[1] . "\n";
			}
			$header .= "\n\n";

			$response['data']['plainbody'] .= $header . $oldMessage['plainbody'];
		}

		if($message instanceof ImapMessage){
			//for saving sent items in actionSend
			$response['sendParams']['forward_uid'] = $message->uid;
			$response['sendParams']['forward_mailbox'] = $params['mailbox'];
			$response['sendParams']['forward_account_id'] = $params['account_id'];
		}

		$this->_keepHeaders($response, $params);

		return $response;
	}

	private function _getFollowUpHeaders(\GO\Email\Model\ComposerMessage $message)
	{
		$lines = array();
		$lines[] = array(GO::t("Subject", "email"), $message->subject);
		$lines[] = array(GO::t("From", "email"), (string) $message->from);
		$lines[] = array(GO::t("To", "email"), (string) $message->to);
		if ($message->cc->count()) {
			$lines[] = array("CC", (string)$message->cc);
		}

		$lines[] = array(GO::t("Date"), \GO\Base\Util\Date::get_timestamp($message->udate));

		return $lines;
	}

	public function actionView($params)
	{
//		Do not close session writing because SMIME stores the password in the session
//		GO::session()->closeWriting();

		$params['no_max_body_size'] = !empty($params['no_max_body_size']) && $params['no_max_body_size']!=='false' ? true : false;

		$account = Account::model()->findByPk($params['account_id']);
		if(!$account) {
			throw new NotFound();
		}

		$imapMessage = ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);

		if(!$imapMessage) {
			throw new NotFound();
		}

		if(!empty($params['create_blobs'])) {
			$imapMessage->createBlobsForAttachments();
		} elseif(!empty($params['create_temporary_attachments'])) {
			$imapMessage->createTempFilesForAttachments();
		}

		$plaintext = !empty($params['plaintext']);

		$response = $imapMessage->toOutputArray(!$plaintext,false,$params['no_max_body_size']);
		$response['uid'] = intval($params['uid']);
		$response['mailbox'] = $params['mailbox'];
		$response['account_id'] = intval($params['account_id']);
		$response['do_not_mark_as_read'] = $account->do_not_mark_as_read;
		$response = $this->_getContactInfo($imapMessage, $params, $response, $account);

		if (!$plaintext) {
			if(empty($response['sender_contact_id']) && $params['mailbox']!=$account->sent && $params['mailbox']!=$account->drafts) {
				$response = $this->_checkXSS($params, $response);
			}

			$response = $this->_blockImages($params, $response);
			$imapMessage->autoLink();

			$response = $this->_handleInvitations($imapMessage, $params, $response);
			
		}
		
		$response['isInSpamFolder']=$this->_getSpamMoveMailboxName($params['uid'],$params['mailbox'],$account->id);

		// START Handle the links div in the email display panel		
		if(!$plaintext){
			$linkedModels = $imapMessage->getLinks();
			$response['links'] = array();
			foreach($linkedModels as $linkedModel){
				$link = $linkedModel->getAttributes();				
				$entityType = \go\core\orm\EntityType::findById($linkedModel->entityTypeId);
				if($entityType) {
					$link['entity'] = $entityType->getName();
					$response['links'][] = $link;				
				}
			}			
		}
		// END OF Handle the links div in the email display panel
		
		
		$this->fireEvent('view', array(
				&$this,
				&$response,
				$imapMessage,
				$account,
				$params
		));

		$response['success'] = true;

		return $response;
	}
	
	
	protected function _getSpamMoveMailboxName($mailUid,$mailboxName,$accountId)
	{
		$pattern = "/^(junk|spam)$/";
		if (preg_match($pattern, strtolower($mailboxName))) {
			return 1;
		} else {
			return 0;
		}
		
	}
	
	
	protected function actionGet($account_id, $mailbox, $uid, $query="")
	{
		return array(
				'success'=>true, 
				'data'=>array(
						'message'=>array(
								'attributes'=>$this->actionView(array('account_id'=>$account_id, 'mailbox'=>$mailbox, 'uid'=>$uid, 'query'=>$query))
								)
						)
				);
	}
	
	protected function actionDelete()
	{
		return array(
				'success'=>true
		);
	}

	private function _getContactInfo(ImapMessage $imapMessage, $params, $response, $account)
	{
		$response['sender_contact_id']=0;
		$response['sender_company_id']=0;
		$response['allow_quicklink']=1;
		$response['contact_name']="";
		$response['contact_thumb_url']=null; //GO::config()->host.'modules/addressbook/themes/Default/images/unknown-person.png';

		$useQL = GO::config()->allow_quicklink;
		$response['allow_quicklink']=$useQL?1:0;

		if($params['mailbox'] === $account->sent) {
			$contact = (!empty($response['to']) && !empty($response['to'][0]['email'])) ?
				\go\modules\community\addressbook\model\Contact::find(['id', 'photoBlobId', 'isOrganization', 'name', 'addressBookId', 'color'])
					->filter(['email' => $response['to'][0]['email'], 'permissionLevel' => \go\core\model\Acl::LEVEL_WRITE])
					->single()
				: false;
		} else {
			$contact = !empty($response['sender']) ?
				\go\modules\community\addressbook\model\Contact::find(['id', 'photoBlobId', 'isOrganization', 'name', 'addressBookId', 'color'])
					->filter(['email' => $response['sender'], 'permissionLevel' => \go\core\model\Acl::LEVEL_WRITE])
					->single()
				: false;
		}
		if(!empty($contact)){
			$response['contact_thumb_url']= go()->getAuthState()->getDownloadUrl($contact->photoBlobId);
			$response['contact'] = $contact->toArray();

			if($useQL){
				$response['sender_contact_id']=$contact->id;
				$response['contact_name']=$contact->name;

				$orgIds = $contact->getOrganizationIds();
				

				$company = isset($orgIds[0]) ? \go\modules\community\addressbook\model\Contact::findById($orgIds[0], ['id', 'name', 'addressBookId']) : null;
				if(!empty($company) && $company->getPermissionLevel() >= \go\core\model\Acl::LEVEL_WRITE){
					$response['sender_company_id']=$company->id;
					$response['company_name']=$company->name;
				}

				if(GO::modules()->savemailas){
					$contactLinkedMessage = \GO\Savemailas\Model\LinkedEmail::model()->findByImapMessage($imapMessage, $contact);
					
					$response['contact_linked_message_id']=$contactLinkedMessage && ($response['contact_link_id'] = $contactLinkedMessage->linkExists($contact)) ? $contactLinkedMessage->id : 0;

					if(!empty($company)){
						$companyLinkedMessage = \GO\Savemailas\Model\LinkedEmail::model()->findByImapMessage($imapMessage, $company);
						$response['company_linked_message_id']=$companyLinkedMessage && ($response['company_link_id'] = $companyLinkedMessage->linkExists($company)) ? $companyLinkedMessage->id : 0;
					}
				}
			}
		}
		return $response;
	}

	private function _checkXSS(array $params, $response)
	{
		if (!empty($params['filterXSS'])) {
			$response['htmlbody'] = \GO\Base\Util\StringHelper::filterXSS($response['htmlbody']);
		} elseif (\GO\Base\Util\StringHelper::detectXSS($response['htmlbody'])) {
			$response['htmlbody'] = GO::t("Message hidden for security reasons", "email");
			$response['xssDetected'] = true;
		} else {
			$response['xssDetected'] = false;
		}
		return $response;
	}

	private function _handleInvitations(ImapMessage $imapMessage, $params, $response)
	{
		if(!Module::isInstalled('community', 'calendar', true)) {
			return $response;
		}

		$vcalendar = $imapMessage->getInvitationVcalendar();
		if($vcalendar) {
			$method = $vcalendar->method->getValue();
			$vevent = $vcalendar->vevent[0];

			$aliases = Alias::model()->find(
				GO\Base\Db\FindParams::newInstance()
					->select('email')
					->criteria(GO\Base\Db\FindCriteria::newInstance()->addCondition('account_id', $imapMessage->account->id))
			)->fetchAll(\PDO::FETCH_COLUMN, 0);

			// for case insensitive match
			$aliases = array_map('strtolower', $aliases);

			//$participants = array_merge($vevent->attendee, [$vevent->organizer]);
			$accountEmail = false;
			if($method ==='REPLY') {
				if (isset($vevent->organizer)) {
					$attendeeEmail = str_replace('mailto:', '', strtolower((string)$vevent->organizer));
					if (in_array($attendeeEmail, $aliases)) {
						$accountEmail = $attendeeEmail;
					}
				}
			} else {
				if (isset($vevent->attendee)) {
					foreach ($vevent->attendee as $vattendee) {
						$attendeeEmail = str_replace('mailto:', '', strtolower((string)$vattendee));
						if (in_array($attendeeEmail, $aliases)) {
							$accountEmail = $attendeeEmail;
						}
					}
				}
			}

			if (!$accountEmail) {
				$response['itip']['feedback'] = GO::t("None of the participants match your e-mail aliases for this e-mail account.", "email");
				return $response;
			}
			$from = $imapMessage->from->getAddress();
			$event = Scheduler::processMessage($vcalendar, $accountEmail, (object)[
				'email'=>$from['email'],
				'name'=>$from['personal']
			]);


			$response['itip'] = [
				'method' => $method,
				'scheduleId' => $accountEmail,
				'event' => $event
			];
			if($method ==='REPLY' && !is_string($event)) {
				$p = $event->participantByScheduleId($from['email']);
				if($p) {
					$lang = go()->t('replyImipBody', 'community', 'calendar');

					$response['itip']['feedback'] = strtr($lang[$p->participationStatus], [
						'{name}' => $p->name ?? '',
						'{title}' => $event->title,
						'{date}' => implode(' ', $event->humanReadableDate()),
					]);
				}
			}

			//filter out invites
			$response['attachments'] = array_values(array_filter($response['attachments'], function($a) {
				return $a['isInvite'] == false;
			}));
		}

		return $response;
	}


	/**
	 * Block external images if sender is not in addressbook.
	 *
	 * @param array $params
	 * @param array $response
	 * @return array
	 */
	private function _blockImages(array $params, array $response)
	{
		if (empty($params['unblock'])){// && !\GO\Addressbook\Model\Contact::model()->findSingleByEmail($response['sender'])) {
			$blockUrl = 'about:blank';
			$response['htmlbody'] = preg_replace("/<([^a]{1})([^>]*)(https?:[^>'\"]*)/iu", "<$1$2" . $blockUrl, $response['htmlbody'], -1, $response['blocked_images']);
		}

		return $response;
	}

	public function actionMessageAttachment(array $params)
	{
		$account = Account::model()->findByPk($params['account_id']);
		
		$tmpFile = \GO\Base\Fs\File::tempFile('message.eml');
		
		$imap = $account->openImapConnection($params['mailbox']);
		
		/* @var $imap \GO\Base\Mail\Imap  */
		
		$imap->save_to_file($params['uid'], $tmpFile->path(), $params['number'], $params['encoding']);
		
		$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($tmpFile->getContents());

		$response = $message->toOutputArray();
		$response = $this->_checkXSS($params, $response);
		$response['path']=$tmpFile->stripTempPath();
		$response['is_tmp_file']=true;
		$response['success']=true;
		return $response;

	}

	private function _tnefAttachment(array $params, Account  $account)
	{
		$tmpFolder = \GO\Base\Fs\Folder::tempFolder(uniqid(time()));
		$tmpFile = $tmpFolder->createChild('winmail.dat');

		$imap = $account->openImapConnection($params['mailbox']);

		$success = $imap->save_to_file($params['uid'], $tmpFile->path(), $params['number'], $params['encoding']);
		if(!$success) {
			throw new \Exception("Could not save temp file for tnef extraction");
		}
		chdir($tmpFolder->path());
		exec(GO::config()->cmd_tnef.' '.$tmpFile->path(), $output, $retVar);
		if($retVar!=0) {
			throw new \Exception("TNEF extraction failed: " . implode("\n", $output));
		}
		$tmpFile->delete();

		$items = $tmpFolder->ls();
		if(!count($items)){
			$this->render("Plain",GO::t("This winmail attachment does not contain any files.", "email"));
			exit();
		}

		exec(GO::config()->cmd_zip.' -r "winmail.zip" *', $output, $retVar);
		if($retVar!=0) {
			throw new \Exception("ZIP compression failed: " . implode("\n", $output));
		}
		
		$zipFile = $tmpFolder->child('winmail.zip');
		\GO\Base\Util\Http::outputDownloadHeaders($zipFile,false,true);
		$zipFile->output();

		$tmpFolder->delete();
	}

	public function actionAttachment(array $params)
	{
		GO::session()->closeWriting();

		$file = new \GO\Base\Fs\File(go()->getTmpFolder()->getPath(). '/' . $params['filename']);

		$account = Account::model()->findByPk($params['account_id']);
		if (strtolower($file->name()) == 'winmail.dat') {
			return $this->_tnefAttachment ($params, $account);
		}
		
		$inline = true;

		if (isset($params['inline']) && $params['inline'] == 0) {
			$inline = false;
		}

		if($file->mimeType() == 'text/html') {
			$inline = false;
		}
		
		$imap = $account->openImapConnection($params['mailbox']);
		
		\GO\Base\Util\Http::outputDownloadHeaders($file,$inline,true);
		$fp =fopen("php://output",'w');
		$imap->get_message_part_decoded($params['uid'], $params['number'], $params['encoding'], false, true, false, $fp);
		fclose($fp);
	}

	protected function actionTnefAttachmentFromTempFile(array $params)
	{
		$tmpFolder = \GO\Base\Fs\Folder::tempFolder(uniqid(time()));
		$tmpFile = new \GO\Base\Fs\File(GO::config()->tmpdir.$params['tmp_file']);

				chdir($tmpFolder->path());
		exec(GO::config()->cmd_tnef.' -C '.$tmpFolder->path().' '.$tmpFile->path(), $output, $retVar);
		if($retVar!=0)
			throw new \Exception("TNEF extraction failed: ".implode("\n", $output));

		exec(GO::config()->cmd_zip.' -r "winmail.zip" *', $output, $retVar);
		if($retVar!=0)
			throw new \Exception("ZIP compression failed: ".implode("\n", $output));
		
		$zipFile = $tmpFolder->child('winmail.zip');
		\GO\Base\Util\Http::outputDownloadHeaders($zipFile,false,true);
		$zipFile->output();

		$tmpFolder->delete();
	}


	protected function actionSaveAttachment(array $params)
	{
		$folder = \GO\Files\Model\Folder::model()->findByPk($params['folder_id']);

		if(!$folder){
			ErrorHandler::log("GO\Email\Controller\Message::actionSaveAttachment(".$params['folder_id'].") folder not found", E_USER_WARNING);
			throw new NotFound("Specified folder not found");
		}

		$params['filename'] = \GO\Base\Fs\File::stripInvalidChars($params['filename']);		
		$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.$folder->path.'/'.$params['filename']);

		if(empty($params['tmp_file'])){
			$account = Account::model()->findByPk($params['account_id']);
			$imap = $account->openImapConnection($params['mailbox']);
			$response['success'] = $imap->save_to_file($params['uid'], $file->path(), $params['number'], $params['encoding']);
		} else {
			$tmpfile = new \GO\Base\Fs\File(GO::config()->tmpdir.$params['tmp_file']);
			$file = $tmpfile->copy($file->parent(), $params['filename']);
			$response['success'] = $file != false;
		}
		
		if(!$folder->hasFile($file->name())) {
			$folder->addFile($file->name());
		}

		if(!$response['success']) {
			$response['feedback'] = 'Could not save to ' . $file->stripFileStoragePath();
		}
		return $response;
	}

	/**
	 * Save an email as a blob, return blob data
	 *
	 * @param array $params [account_id, uid, mailbox, number, encoding]
	 *
	 * @return array
	 * @throws NotFound
	 * @throws AccessDenied
	 * @throws Exception
	 */
	protected function actionSaveToBlob(array $params): array
	{
		$account = Account::model()->findByPk($params['account_id']);

		try {
			$imap = $account->openImapConnection($params['mailbox']);
		} catch (GO\Base\Mail\Exception\ImapAuthenticationFailedException|GO\Base\Mail\Exception\MailboxNotFound $e) {
			return [
				'success' => false,
				'feedback' => $e->getMessage()
			];
		}
		$message = ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
		if(!$message) {
			throw new NotFound();
		}
		$fileName = FileSystemObject::stripInvalidChars((strlen($message->subject)  ? $message->subject : GO::t('No subject')) . '.eml');
		$tmpFile = File::tempFile('eml');

		$imap->save_to_file($params['uid'], $tmpFile->getPath(), $params['number'], $params['encoding']);

		$blob = Blob::fromTmp($tmpFile);
		$blob->save();

		$blobData = [
			'extension' => $tmpFile->getExtension(),
			'size' => $blob->size,
			'type' =>  $blob->type,
			'name' =>  $fileName,
			'fileName' =>  $fileName,
			'from_file_storage' => true,
			'tmp_file' => $tmpFile->getPath(),
			'id' => $blob->id

		];

		return ['success' => true, 'blob' => $blobData];
	}

	/**
	 * Save all attachments of the given message to the given folder
	 * 
	 * @param int $folder_id		The id of the folder to save the attachments to
	 * @param int $account_id		The account id of the mailbox account
	 * @param string $mailbox		The affected mailbox in where to search the message uid
	 * @param int $uid					The uid of the message to search in the mailbox
	 * 
	 * @return array				['success' => bool, 'message' => ?string]
	 * 
	 * @throws NotFound
	 * @throws AccessDenied
	 */
	protected function actionSaveAllAttachments($folder_id,$account_id,$mailbox,$uid, $filepath = null): array
	{
		$response = array('success'=>true);
		
		$folder = \GO\Files\Model\Folder::model()->findByPk($folder_id);

		if(!$folder){
			trigger_error("GO\Email\Controller\Message::actionSaveAllAttachments(".$folder_id.") folder not found", E_USER_WARNING);
			throw new NotFound("Specified folder not found");
		}
		
		if(!$folder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION)) {
			throw new \GO\Base\Exception\AccessDenied();
		}
		
		
		if(!empty($filepath)) {
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeFile($filepath);
		} else {
			// Search message from imap
			$account = Account::model()->findByPk($account_id);

			if(!$account){
				trigger_error("GO\Email\Controller\Message::actionSaveAllAttachments(".$account_id.") account not found", E_USER_WARNING);
				throw new NotFound("Specified account not found");
			}

			$message = ImapMessage::model()->findByUid($account, $mailbox, $uid);
		}
		if(!$message){
			trigger_error("GO\Email\Controller\Message::actionSaveAllAttachments(". $mailbox." - ". $uid.") message not found", E_USER_WARNING);
			throw new NotFound("Specified message could not be found");
		}
		
		$atts = $message->getAttachments();
		$fsFolder = $folder->fsFolder;
		//\GO::debug($atts);
		while($att=array_shift($atts)){
			if(empty($att->content_id) || $att->disposition=='attachment'){
		
				// Check if the file already exists on disk, if so then add a number after it.
				$fileName = null;
				$file = $fsFolder->child($att->name);
				if($file){
					$file->appendNumberToNameIfExists();
					$fileName = $file->name();
				}
				
				if(!$att->saveToFile($fsFolder,$fileName)){
					$response['success'] = false;
				}
			}
		}

		if(!$response['success']){
			$response['feedback']='Could not save all files to the selected folder';
		}
		
		// Call syncFilesystem on the folder because otherwise the files are not yet visible in the database.
		$folder->syncFilesystem();
		
		return $response;
	}

	protected function actionSource(array $params)
	{

		$account = Account::model()->findByPk($params['account_id']);
		$imap  = $account->openImapConnection($params['mailbox']);

		$message = ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
		
		$filename = GO\Base\Fs\File::stripInvalidChars($message->subject.' - '.\GO\Base\Util\Date::get_timestamp($message->udate));
		$filename .= empty($params['download']) ? ".txt" :".eml";
		
		\GO\Base\Util\Http::outputDownloadHeaders(new \GO\Base\Fs\File($filename), empty($params['download']));

		/*
		 * Somehow fetching a message with an empty message part which should fetch it
		 * all doesn't work. (http://tools.ietf.org/html/rfc3501#section-6.4.5)
		 *
		 * That's why I first fetch the header and then the text.
		 */
		$header = $imap->get_message_part($params['uid'], 'HEADER', true) . "\r\n\r\n";
		$size = $imap->get_message_part_start($params['uid'], 'TEXT', true);

		header('Content-Length: ' . (strlen($header) + $size));

		echo $header;
		while ($line = $imap->get_message_part_line()) {
			echo $line;
		}
	}

	protected function actionMoveOld(array $params)
	{
		$this->checkRequiredParameters(array('mailbox','target_mailbox'), $params);

		if($params['mailbox']==$params['target_mailbox']) {
			throw new \Exception(GO::t("Source and target mailbox may not be the same", "email"));
		}

		$account = Account::model()->findByPk($params['account_id']);
		$imap  = $account->openImapConnection($params['mailbox']);

		$before_timestamp = \GO\Base\Util\Date::to_unixtime($params['until_date']);
		if (empty($before_timestamp)) {
			throw new \Exception(GO::t("I tried to process the following \"Until Date\", but the processing stopped because an error occurred", "email") . ': ' . $params['until_date']);
		}
		$date_string = date('d-M-Y',$before_timestamp);

		$uids = $imap->sort_mailbox('ARRIVAL',false,'BEFORE "'.$date_string.'"');

		$response['total']=count($uids);
		$response['success'] = true;
		if($response['total']){
			$chunks = array_chunk($uids, 1000);
			while($uids=array_shift($chunks)) {
				if(!$imap->move($uids, $params['target_mailbox'])) {
					throw new \Exception("Could not move mails! ".$imap->last_error());
				}
			}
		}
		return $response;
	}


	/**
	 * This action will move imap messages from one folder to another
	 *
	 * @param array $params
	 * - string messages: json encoded message uid's
	 * - int total: total messages to be moved
	 * - int from_account_id: the GO email account id the messages should be moved from
	 * - int to_account_id: the GO email account id the message should be moved to
	 * - string from_mailbox: the imap mailbox name to move messages from
	 * - string to_mailbox: the imap mailbox name to move messages to
	 * @return array $response
	 * @throws Exception when moving a message fails
	 */
	protected function actionMove(array $params)
	{
			$start_time = time();

			$messages= json_decode($params['messages'], true);
			$total = $params['total'];

			//move to another imap account
			$from_account = Account::model()->findByPk($params['from_account_id']);
			$to_account = Account::model()->findByPk($params['to_account_id']);

			if(!$from_account->checkPermissionLevel(Acl::CREATE_PERMISSION)) {
				throw new \GO\Base\Exception\AccessDenied();
			}

			if(!$to_account->checkPermissionLevel(Acl::CREATE_PERMISSION)) {
				throw new \GO\Base\Exception\AccessDenied();
			}

			$imap = $from_account->openImapConnection($params['from_mailbox']);
			$imap2 = $to_account->openImapConnection($params['to_mailbox']);

			$delete_messages =array();
			while($uid=array_shift($messages)) {
				$source = $imap->get_message_part($uid);

				$header = $imap->get_message_header($uid);

				$flags = '\Seen';
				if(!empty($header['flagged'])) {
					$flags .= ' \Flagged';
				}
				if(!empty($header['answered'])) {
					$flags .= ' \Answered';
				}
				if(!empty($header['forwarded'])) {
					$flags .= ' $Forwarded';
				}

				if(!$imap2->append_message($params['to_mailbox'], $source, $flags)) {
					$imap2->disconnect();
					throw new \Exception('Could not move message');
				}

				$delete_messages[]=$uid;

				$left = count($messages);

				if($left && $start_time-5<time()) {

					$done = $total-$left;

					$response['messages']=$messages;
					$response['progress']=number_format($done/$total,2);

					break;
				}
			}
			$imap->delete($delete_messages);

			$imap2->disconnect();
			$imap->disconnect();

			$response['success'] = true;

			return $response;
	}

	/**
	 * Delete all attachments from current email message
	 *
	 * @param array $params
	 * @return bool[]
	 * @throws AccessDenied
	 */
	protected function actionDeleteAllAttachments(array $params): array
	{
		$account = Account::model()->findByPk($params['account_id']);
		$response = ['success' => true];
		$message = ImapMessage::model()->findByUid($account, $params["mailbox"], $params["uid"]);
		if ($message->deleteAttachments()) {
			$message->delete();
			$message->getImapConnection()->expunge();
			$response['uid'] = $message->getImapConnection()->get_uidnext();
		}

		return $response;
	}

	protected function actionZipAllAttachments(array $params)
	{
		$account = Account::model()->findByPk($params['account_id']);

		$message = ImapMessage::model()->findByUid($account, $params["mailbox"], $params["uid"]);

		$tmpFolder = \GO\Base\Fs\Folder::tempFolder(uniqid(time()));
		$atts = $message->getAttachments();
		while($att=array_shift($atts)){
			if($att->disposition == 'attachment' || empty($att->content_id)) {
				$att->saveToFile($tmpFolder);
			}
		}

		$archiveFile = $tmpFolder->parent()->createChild(GO::t("Attachments", "email").'.zip');

		\GO\Base\Fs\Zip::create($archiveFile, $tmpFolder, $tmpFolder->ls());


		\GO\Base\Util\Http::outputDownloadHeaders($archiveFile, false);

		readfile($archiveFile->path());

		$tmpFolder->delete();
		$archiveFile->delete();

	}

	protected function actionMoveToSpam(array $params)
	{

		$account = Account::model()->findByPk($params['account_id']);
		$imap = $account->openImapConnection($params['from_mailbox_name']);
		$spamFolder = isset(GO::config()->spam_folder) ? GO::config()->spam_folder : $account->spam;

		if (empty($spamFolder)) {
			$spamFolder = 'Spam';
		}

		if(!$imap->get_status($spamFolder)){
			$imap->create_folder($spamFolder);
		}

		$params['mail_uid'] = json_decode($params['mail_uid']);
		$uids = is_array($params['mail_uid']) ? $params['mail_uid'] : array($params['mail_uid']);
							
		if (!$imap->move($uids, $spamFolder)) {
			$imap->disconnect();
			throw new \Exception('Could not move message to "'.$spamFolder.'" folder. Does it exist?');
		}
		
		$response = array('success'=>true);
		echo json_encode($response);
	}
	
	protected function actionMoveToInbox(array $params)
	{
		
		$spamFolder = isset(GO::config()->spam_folder) ? GO::config()->spam_folder : 'Spam';
		
		$accountModel = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
				
		$imap = $accountModel->openImapConnection($spamFolder);
							
		if (!$imap->move(array($params['mail_uid']),'INBOX')) {
			$imap->disconnect();
			throw new \Exception('Could not move message');
		}
		
		$response = array('success'=>true);
		echo json_encode($response);
		
	}
	
}
