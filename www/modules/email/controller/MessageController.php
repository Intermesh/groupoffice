<?php


namespace GO\Email\Controller;

use GO;
use GO\Base\Exception\AccessDenied;

use go\core\model\User;
use GO\Email\Model\Account;
use GO\Email\Model\Alias;
use GO\Email\Model\Label;

use GO\Base\Model\Acl;

use GO\Base\Mail\Imap;
use go\core\model\Acl as GoAcl;
use go\core\util\ArrayObject;
use go\modules\community\addressbook\model\Contact;
use go\modules\community\addressbook\model\Settings;
use go\modules\community\addressbook\Module;
use GO\Email\Model\ContactMailTime;

class MessageController extends \GO\Base\Controller\AbstractController {

	protected function allowGuests() {
		return array("mailto");
	}
	/*
	 * Example URL: http://localhost/groupoffice-4.0/www/?r=email/message/mailto&mailto=mailto:info@intermesh.nl&bcc=test@intermesh.nl&body=jaja&cc=cc@intermesh.nl&subject=subject
	 */
	protected function actionMailto($params){
		$qs=strtolower(str_replace('mailto:','', urldecode($_SERVER['QUERY_STRING'])));
		$qs=str_replace('?subject','&subject', $qs);

		parse_str($qs, $vars);


		$vars['to']=isset($vars['mailto']) ? $vars['mailto'] : '';
		unset($vars['mailto'], $vars['r']);

		if(!isset($vars['subject']))
			$vars['subject']='';

		if(!isset($vars['body']))
			$vars['body']='';
		//
//		var_dump($vars);
//		exit();

		header('Location: '.GO::createExternalUrl('email', 'showComposer', array('values'=>$vars)));
		exit();
	}

	protected function actionNotification($params){
		$account = Account::model()->findByPk($params['account_id']);
		
		$alias = $this->_findAliasFromRecipients($account, new \GO\Base\Mail\EmailRecipients($params['message_to']));	
		if(!$alias)
			$alias = $account->getDefaultAlias();

		$body = sprintf(GO::t("Your message with subject \"%s\" was displayed at %s", "email"), $params['subject'], \GO\Base\Util\Date::get_timestamp(time()));

		$message = new \GO\Base\Mail\Message(
						sprintf(GO::t("Read: %s", "email"),$params['subject']),
						$body
						);
		$message->setFrom($alias->email, $alias->name);
		$toList = new \GO\Base\Mail\EmailRecipients($params['notification_to']);
		$address=$toList->getAddress();
		$message->setTo($address['email'], $address['personal']);

		$mailer = \GO\Base\Mail\Mailer::newGoInstance(\GO\Email\Transport::newGoInstance($account));
		$response['success'] = $mailer->send($message);

		return $response;
	}


	private function _moveMessages($imap, $params, &$response, $account){
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

	private function _filterMessages($mailbox, Account $account) {

		$filters = $account->filters->fetchAll();

		if (count($filters)) {
			$imap = $account->openImapConnection($mailbox);

			$messages = \GO\Email\Model\ImapMessage::model()->find($account, $mailbox,0, 100, Imap::SORT_ARRIVAL, false, "UNSEEN");
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
						if ($filter->mark_as_read)
							$imap->set_message_flag($matches, "\Seen");

						$imap->move($matches, $filter->folder);
					}
				}
			}
		}
	}

	protected function actionTestSearch(){

		$imapSearch = new \GO\Email\Model\ImapSearchQuery();

//		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::TO);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::TO);
//
//		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::BCC);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::BCC);
//
	//	$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::CC);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::CC);
//
//		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::FROM);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::FROM);
//
//		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::BODY);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::BODY);
//
		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::SUBJECT);
		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::SUBJECT);
//
//		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::TEXT);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::TEXT);
//
//		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::KEYWORD);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::KEYWORD);

//		$imapSearch->addSearchWord('test', \GO\Email\Model\ImapSearchQuery::UNKEYWORD);
//		$imapSearch->addSearchWord('test2', \GO\Email\Model\ImapSearchQuery::UNKEYWORD);

	//		$imapSearch->searchAll();
//		$imapSearch->searchAnswered();
//		$imapSearch->searchDeleted();
//		$imapSearch->searchFlagged();
//		$imapSearch->searchNew();
		$imapSearch->searchOld();
//		$imapSearch->searchRecent();
//		$imapSearch->searchSeen();
//		$imapSearch->searchUnDeleted();
//		$imapSearch->searchUnFlagged();
//		$imapSearch->searchUnSeen();
//		$imapSearch->searchUnanswered();

//		$imapSearch->searchSince();
//		$imapSearch->searchOn();
//		$imapSearch->searchBefore();

		$command = $imapSearch->getImapSearchQuery();

		echo $command."</br>";

		$account = Account::model()->findByPk(145);
		$imap = $account->openImapConnection('INBOX');

		$messages = \GO\Email\Model\ImapMessage::model()->find(
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

	protected function actionStore($params){

		$this->checkRequiredParameters(array('account_id'), $params);

		GO::session()->closeWriting();

		if(!isset($params['start']))
			$params['start']=0;

		if(!isset($params['limit']))
			$params['limit']=GO::user()->max_rows_list;

		if(!isset($params['dir']))
			$params['dir']="ASC";

		$query=isset($params['query']) ? $params['query'] : "";

		//passed when only unread should be shown
		if(!empty($params['unread'])) {
			$query = str_replace(array('UNSEEN', 'SEEN'), array('', ''), $query);
			if ($query == '')
				$query .= 'UNSEEN';
			else
				$query.= ' UNSEEN';
		}
		if(!empty($params['flagged'])) {
			$query = str_replace(array('UNFLAGGED', 'FLAGGED'), array('', ''), $query);
			if ($query == '')
				$query .= 'FLAGGED';
			else
				$query.= ' FLAGGED';
		}

		$account = Account::model()->findByPk($params['account_id']);
		if(!$account)
			throw new \GO\Base\Exception\NotFound();
		/* @var $account Account */

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
			case 'arrival':
				$sortField=Imap::SORT_ARRIVAL; //arrival is faster on older mail servers
				break;

			case 'date':
				$sortField=Imap::SORT_DATE; //arrival is faster on older mail servers
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

//		$imap = $account->openImapConnection($params["mailbox"]);

		//
		if(!empty($params['delete_keys'])){

			if(!$account->checkPermissionLevel(Acl::CREATE_PERMISSION))
			  $response['deleteFeedback']=GO::t("You don't have permission to perform this action");
			else {
				$uids = json_decode($params['delete_keys']);

				if(!$response['trash'] && !empty($account->trash)) {
					$imap->set_message_flag($uids, "\Seen");
					$response['deleteSuccess']=$imap->move($uids,$account->trash);
				}else {

					$response['deleteSuccess']=$imap->delete($uids);
				}
				if(!$response['deleteSuccess']) {
					$lasterror = $imap->last_error();
					if(stripos($lasterror,'quota')!==false) {
						$response['deleteFeedback']=GO::t("Your mailbox is full. Empty your trash folder first. If it is already empty and your mailbox is still full, you must disable the Trash folder to delete messages from other folders. You can disable it at:

Settings -> Accounts -> Double click account -> Folders.", "email");
					}else {
						$response['deleteFeedback']=GO::t("Error while deleting the data").":\n\n".$lasterror."\n\n".GO::t("Moving the e-mail to the trash folder failed. This might be because you are out of disk space. You can only free up space by disabling the trash folder at Administration -> Accounts -> Double click your account -> Folders", "email");
					}
				}
			}
		}


		//make sure we are connected to the right mailbox after move and delete operations
//		$imap = $account->openImapConnection($params["mailbox"]);

		$response['multipleFolders']=false;
		$searchIn = 'current'; //default to current if not set
		if(isset($params['searchIn']) && in_array($params['searchIn'], array('all', 'recursive'))) {
				$searchIn = $params['searchIn'];
				$response['multipleFolders'] = true;
		}

		$messages = \GO\Email\Model\ImapMessage::model()->find(
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
		foreach($messages as $message){

			$record = $message->getAttributes(true);
			
			$messageMailbox = new \GO\Email\Model\ImapMailbox($message->account, array('name'=>$message->mailbox));
			$record['mailboxname'] = $messageMailbox->getDisplayName();
		
			$record['account_id']=$account->id;

			if(!isset($record['mailbox']))
				$record['mailbox']=$params["mailbox"];

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
			foreach($addresses as $email=>$personal)
			{
				$to[]=empty($personal) ? $email : $personal;
			}
			$record['to']=  htmlspecialchars(implode(',', $to), ENT_COMPAT, 'UTF-8');
			
			if($response['sent'] || $response['drafts']){

				$to = $record['to'];
				$record['to'] = $record['from'];
				$record['from'] = $to;
			}else
			{
				$record = $this->checkPersonalField($record, $message);
			}

			if(empty($record['subject']))
				$record['subject']=GO::t("No subject", "email");
			else
				$record['subject'] = htmlspecialchars($record['subject'],ENT_COMPAT,'UTF-8');



			$response["results"][]=$record;
		}

		$response['total'] = $imap->sort_count;

		//$unseen = $imap->get_unseen($params['mailbox']);

		$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name'=>$params['mailbox']));
		$mailbox->snoozeAlarm();

		$response['unseen'][$params['mailbox']]=$mailbox->unseen;

		//deletes must be confirmed if no trash folder is used or when we are in the trash folder to delete permanently
		$response['deleteConfirm']=empty($account->trash) || $account->trash==$params['mailbox'];

		return $response;
	}
	
	private function checkPersonalField($record, $message) {
		
		$from = $message->from->getAddress();
						
		if(\GO\Base\Util\Validate::email(($record['from'])) && strtolower($record['from']) != strtolower($from['email'])) {
			$record['from'] = '<div style="color: red">'.$from['email'].'</div>';
		}
		
		return $record;
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
	 * @return type
	 */
	protected function actionSetFlag($params){

		GO::session()->closeWriting();

		$messages = json_decode($params['messages']);

		$account = Account::model()->findByPk($params['account_id']);

		$requiredPermissionLevel = $params["flag"]=='Seen' && !empty($params["clear"]) ? Acl::CREATE_PERMISSION : Account::ACL_DELEGATED_PERMISSION;

		if(!$account->checkPermissionLevel($requiredPermissionLevel))
		  throw new \GO\Base\Exception\AccessDenied();

		$imap = $account->openImapConnection($params["mailbox"]);

		if (in_array(ucfirst($params['flag']), Imap::$systemFlags)) {
			$params["flag"] = "\\".ucfirst($params["flag"]);
		}

		$response['success']=$imap->set_message_flag($messages, $params["flag"], !empty($params["clear"]));

		$mailbox = new \GO\Email\Model\ImapMailbox($account, array('name'=>$params['mailbox']));
		$mailbox->snoozeAlarm();

		$response['unseen']=$mailbox->unseen;

		return $response;
	}


	private function _link($params, \GO\Base\Mail\Message $message, $tags=array()) {

		$autoLinkContacts = \go\core\model\Module::isInstalled('community','addressbook') &&  Settings::get()->autoLinkEmail;
		

		if (!empty($params['link']) || $autoLinkContacts || count($tags)) {

			$path = 'email/' . date('mY') . '/sent_' .\GO::user()->id.'-'. uniqid(time()) . '.eml';

			$file = new \GO\Base\Fs\File(GO::config()->file_storage_path . $path);
			$file->parent()->create();

			$fbs = new \Swift_ByteStream_FileByteStream($file->path(), true);
			$message->toByteStream($fbs);

			if (!$file->exists()) {
				throw new \Exception("Failed to save email to file!");
			}

				$attributes = array();


				
				$alias = \GO\Email\Model\Alias::model()->findByPk($params['alias_id']);

				$attributes['from'] = (string) \GO\Base\Mail\EmailRecipients::createSingle($alias->email, $alias->name);
				if (isset($params['to']))
					$attributes['to'] = $params['to'];

				if (isset($params['cc']))
					$attributes['cc'] = $params['cc'];

				if (isset($params['bcc']))
					$attributes['bcc'] = $params['bcc'];

				$attributes['subject'] = !empty($params['subject']) ? $params['subject'] : GO::t("No subject", "email");
				//


				$attributes['path'] = $path;

				$date = $message->getDate();
								
				$attributes['time'] = $date ? $date->format('U') : time();
				$attributes['uid']= '<'.$message->getId().'>';// $alias->email.'-'.$message->getDate();

				$linkedModels = new \go\core\util\ArrayObject();
				
				if(!empty($link)) {
					//add link sent by composer as a tag to unify code
					$linkProps = explode(':', $params['link']);
					$tags = array_unshift($tags, ['model' => $linkProps[0], 'model_id' => $linkProps[1]]);
				}
				
				//process tags in the message body
				while($tag = array_shift($tags)){			
					$linkModel = \GO\Savemailas\SavemailasModule::getLinkModel($tag['model'], $tag['model_id']);
					if($linkModel && $linkedModels->findKeyBy(function($item) use ($linkModel) { return $item->equals($linkModel); } ) === false){
						
						$attributes['acl_id']=$linkModel->findAclId();
						
						$linkedEmail = \GO\Savemailas\Model\LinkedEmail::model()->findSingleByAttributes(array(
							'uid'=>$attributes['uid'], 
							'acl_id'=>$attributes['acl_id']));

						if(!$linkedEmail){
							$linkedEmail = new \GO\Savemailas\Model\LinkedEmail();
							$linkedEmail->setAttributes($attributes);
							$linkedEmail->save();
						}


						$linkedEmail->link($linkModel);

						$linkedModels[]=$linkModel;
					}					
				}
				
				
				if($autoLinkContacts){
					$to = new \GO\Base\Mail\EmailRecipients($params['to'].",".$params['bcc']);
					$to = $to->getAddresses();

//					var_dump($to);

					foreach($to as $email=>$name){
						
						$contacts = Contact::findByEmail($email, ['id', 'addressBookId'])->filter(['permissionLevel' => GoAcl::LEVEL_WRITE]);

						foreach($contacts as $contact){

						if($contact && $linkedModels->findKeyBy(function($item) use ($contact) { return $item->equals($contact); } ) === false){						

							$attributes['acl_id']= $contact->findAclId();
							
							$linkedEmail = \GO\Savemailas\Model\LinkedEmail::model()->findSingleByAttributes(array(
								'uid'=>$attributes['uid'], 
								'acl_id'=>$attributes['acl_id']));
							
							if(!$linkedEmail){
								$linkedEmail = new \GO\Savemailas\Model\LinkedEmail();
								$linkedEmail->setAttributes($attributes);
								$linkedEmail->save();
							}

							$linkedEmail->link($contact);
						}
							
							// Also link the company to the email if the contact has a company attached to it.
						// 	if(!empty(GO::config()->email_autolink_companies) && !empty($contact->company_id)){
						// 		$company = $contact->company;
						// 		if($company && !$company->equals($linkedModels)){
						// 			$linkedEmail->link($company);
						// 		}
						// 	}
						}
					}
				}
			
		}
	}

	protected function actionSave($params) {

		GO::session()->closeWriting();

		$alias = \GO\Email\Model\Alias::model()->findByPk($params['alias_id']);
		$account = Account::model()->findByPk($alias->account_id);

		if (empty($account->drafts))
			throw new \Exception(GO::t("Message could not be saved because the 'Drafts' folder is disabled.<br /><br />Go to E-mail -> Administration -> Accounts -> Double click account -> Folders to configure it.", "email"));

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

		if(!$imap->append_message($account->drafts, $message, "\Seen")){
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


	protected function actionSaveToFile($params){
		$message = new \GO\Base\Mail\Message();
		$alias = \GO\Email\Model\Alias::model()->findByPk($params['alias_id']);
		$message->handleEmailFormInput($params);
		$message->setFrom($alias->email, $alias->name);

		$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.$params['save_to_path']);

		$fbs = new \Swift_ByteStream_FileByteStream($file->path(), true);

		$message->toByteStream($fbs);

		$response['success']=$file->exists();

		return $response;
	}

	private function _createAutoLinkTagFromParams($params, $account){
		$tag = '';
		if (!empty($params['links'])) {
			$links = json_decode($params['links'], true);
			
			foreach($links as $link) {			
				$tag .= $this->_createAutoLinkTag($account,$link['toEntity'],$link['toId']);
			}
		}
		return $tag;
	}


	private function _createAutoLinkTag($account, $model_name, $model_id){
		return "[link:".base64_encode($_SERVER['SERVER_NAME'].','.$account->id.','.$model_name.','.$model_id)."]";
	}

	private function _findUnknownRecipients($params) {

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
	 * @param type $params
	 * @return boolean
	 */
	protected function actionSend($params) {

		GO::session()->closeWriting();

		$response['success'] = true;
		$response['feedback']='';

		$alias = \GO\Email\Model\Alias::model()->findByPk($params['alias_id']);
		$account = Account::model()->findByPk($alias->account_id);

		$message = new \GO\Base\Mail\SmimeMessage();

		$tag = $this->_createAutoLinkTagFromParams($params, $account);

		if(!empty($tag)){
			if($params['content_type']=='html')
				$params['htmlbody'].= '<div style="display:none">'.$tag.'</div>';
			else
				$params['plainbody'].= "\n\n".$tag."\n\n";
		}

		$message->handleEmailFormInput($params);
    $recipientCount = $message->countRecipients();

		if(!$recipientCount)
			throw new \Exception(GO::t("You didn't enter a recipient", "email"));

		$message->setFrom($alias->email, $alias->name);
		
		$mailer = \GO\Base\Mail\Mailer::newGoInstance(\GO\Email\Transport::newGoInstance($account));

		$logger = new \Swift_Plugins_Loggers_ArrayLogger();
		$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));


		$this->fireEvent('beforesend', array(
				&$this,
				&$response,
				&$message,
				&$mailer,
				$account,
				$alias,
				$params
		));

		$failedRecipients=array();	
		
		$success = $mailer->send($message, $failedRecipients);		

//		// Update "last mailed" time of the emailed contacts.
		if ($success && GO::modules()->addressbook) {

			$toAddresses = $message->getTo();
			if (empty($toAddresses))
				$toAddresses = array();
			$ccAddresses = $message->getCc();
			if (empty($ccAddresses))
				$ccAddresses = array();
			$bccAddresses = $message->getBcc();
			if (empty($bccAddresses))
				$bccAddresses = array();
			$emailAddresses = array_merge($toAddresses,$ccAddresses);
			$emailAddresses = array_merge($emailAddresses,$bccAddresses);

			foreach ($emailAddresses as $emailAddress => $fullName) {

				$contact = Contact::findByEmail($emailAddress)->orderBy(['c.goUserId' => 'DESC'])->single();

				if($contact) {
					$contactLastMailTimeModel = ContactMailTime::model()->findSingleByAttributes(array(
						'contact_id' => $contact->id,
						'user_id' => GO::user()->id
					));

					if (!$contactLastMailTimeModel) {
						$contactLastMailTimeModel = new ContactMailTime();
						$contactLastMailTimeModel->contact_id = $contact->id;
						$contactLastMailTimeModel->user_id = GO::user()->id;
					}

					$contactLastMailTimeModel->last_mail_time = time();
					$contactLastMailTimeModel->save();
				}


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
		if ($account->ignore_sent_folder && !empty($params['reply_mailbox']))
			$account->sent = $params['reply_mailbox'];


		if ($account->sent && $recipientCount > count($failedRecipients)) {

			GO::debug("Sent");
			//if a sent items folder is set in the account then save it to the imap folder
			$imap = $account->openImapConnection($account->sent);
			if(!$imap->append_message($account->sent, $message, "\Seen")){
				$response['success']=false;
				$response['feedback'].='Failed to save send item to '.$account->sent;
			}
		}		

		if (!empty($params['draft_uid'])) {
			//remove drafts on send
			$imap = $account->openImapConnection($account->drafts);
			$imap->delete(array($params['draft_uid']));
		}
		
		if(count($failedRecipients)){

			$msg = GO::t("Failed to send to", "email").': '.implode(', ',$failedRecipients).'<br /><br />';

			$logStr = $logger->dump();

			preg_match('/<< 55[0-9] .*>>/s', $logStr, $matches);

			if (isset($matches[0])) {
				$logStr = trim(substr($matches[0], 2, -2));
			}

			throw new \Exception($msg.nl2br($logStr));
		}
		
		//if there's an autolink tag in the message we want to link outgoing messages too.
		$tags = $this->_findAutoLinkTags($params['content_type']=='html' ? $params['htmlbody'] : $params['plainbody'], $account->id);
		
		$this->_link($params, $message, $tags);

		$response['unknown_recipients'] = $this->_findUnknownRecipients($params);


		return $response;
	}

	private function _addEmailsAsAttachment($message, $params){
		if(!empty($params['addEmailAsAttachmentList'])) {
			$addEmailAsAttachmentList = json_decode($params['addEmailAsAttachmentList']);
			$account = Account::model()->findByPk($params['account_id']);
			$numberAttachment = 1;
			foreach ($addEmailAsAttachmentList as $value) {

					$attachmentMessage = \GO\Email\Model\ImapMessage::model()->findByUid($account, $value->mailbox, $value->uid);

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
	
	public function loadTemplate($params) {
		
		
		$unsetSubject = true;
		
		if (!empty($params['template_id'])) {
			try {
				$template = \GO\Base\Model\Template::model()->findByPk($params['template_id']);
				$templateContent = $template ? $template->content : '';
			} catch (\GO\Base\Exception\AccessDenied $e) {
				$templateContent = "";
			}
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($templateContent);
			
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
					'contact:salutation'=>GO::t("Dear Mr / Ms")
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

//					$company = false;
//					if(!empty($params['company_id']))
//						$company = \GO\Addressbook\Model\Company::model()->findByPk($params['company_id']);
//
//					if($company){
//						$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceModelTags($response['data']['htmlbody'], $company,'company:',true);
//					}

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
				
				if(!empty($params['alias_id']) && ($alias = GO\Email\Model\Alias::model()->findByPk($params['alias_id']))) {				
//					var_dump($response['data']['htmlbody']);
					
					$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceModelTags($response['data']['htmlbody'], $alias, 'alias:', true);
				}
				
//				if(!empty($params['account_id']) && ($alias = Account::model()->findByPk($params['account_id']))) {				
////					var_dump($response['data']['htmlbody']);
//					
//					$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceModelTags($response['data']['htmlbody'], $account, 'account:', true);
//				}
				
				//cleanup empty tags
				$response['data']['htmlbody'] = \GO\Base\Model\Template::model()->replaceCustomTags($response['data']['htmlbody'],['body' => $params['body'] ?? ""], false);
			}

			if ($params['content_type'] == 'plain') {
				$response['data']['plainbody'] = \GO\Base\Util\StringHelper::html_to_text($response['data']['htmlbody'], false);
				unset($response['data']['htmlbody']);
			}
		} else {
			$message = new \GO\Email\Model\ComposerMessage();
			
			$this->_setAddressFields($params, $message);
			$this->_addEmailsAsAttachment($message,$params);
			
			$response['data'] = $message->toOutputArray($params['content_type'] == 'html', true);

			if(isset($params['body'])) {
				$response['data']['htmlbody'] = $params['body'] . '<br />' . $response['data']['htmlbody'];
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
	private function _setAddressFields($params,$message){
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
	
	private function getTempDir($accountId, $mailbox, $uid){
		$this->_tmpDir=\GO::config()->tmpdir.'imap_messages/'.$accountId.'-'.$mailbox.'-'.$uid.'/';
		if(!is_dir($this->_tmpDir))
			mkdir($this->_tmpDir, 0700, true);
		return $this->_tmpDir;
	}
	
	/**
	 * When changing content type or template in email composer we don't want to
	 * reset some header fields.
	 *
	 * @param type $response
	 * @param type $params
	 */
	private function _keepHeaders(&$response, $params, $unsetSubject = true) {
		if (!empty($params['keepHeaders'])) {
			unset(
							$response['data']['alias_id'],
							$response['data']['to'], 
							$response['data']['cc'], 
							$response['data']['bcc']
//							$response['data']['attachments']
			);
			
			if($unsetSubject) {
				unset($response['data']['subject']);
			}
		}
	}

	protected function actionTemplate($params) {
		$response = $this->loadTemplate($params);
		
		
//		$this->_keepHeaders($response, $params);
		return $response;
	}

	private function _quoteHtml($html) {
		return '<blockquote style="border:0;border-left: 2px solid #22437f; padding:0px; margin:0px; padding-left:5px; margin-left: 5px; ">' .
						$html .
						'</blockquote>';
	}

	private function _quoteText($text) {
		$text = \GO\Base\Util\StringHelper::normalizeCrlf($text, "\n");

		return '> ' . str_replace("\n", "\n> ", $text);
	}

	protected function actionOpenDraft($params) {
		if(!empty($params['uid'])){
			$account = Account::model()->findByPk($params['account_id']);
			$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
			$message->createTempFilesForAttachments();
			$response['sendParams']['draft_uid'] = $message->uid;
		}else
		{
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeFile($params['path']);
		}
		$response['data'] = $message->toOutputArray($params['content_type'] == 'html', true,false,false);

		if(!empty($params['uid'])){
			$alias = $this->_findAliasFromRecipients($account, $message->from,0,true);	
			
			if($alias)
				$response['data']['alias_id']=$alias->id;
		}

		$response['success'] = true;
		return $response;
	}

	/**
	 * Reply to a mail message. It can handle an IMAP message or a saved message.
	 *
	 * @param type $params
	 * @return type
	 */
	protected function actionReply($params){

		if(!empty($params['uid'])){
			$account = Account::model()->findByPk($params['account_id']);
			if(!$account)
				throw new \GO\Base\Exception\NotFound();

			$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
			if(!$message)
				throw new \GO\Base\Exception\NotFound();
		}else
		{
			$account=false;
			$message =  \GO\Email\Model\SavedMessage::model()->createFromMimeFile($params['path'], !empty($params['is_tmp_file']) && $params['is_tmp_file']!='false');
		}

		return $this->_messageToReplyResponse($params, $message, $account);
	}

	private function _messageToReplyResponse($params, \GO\Email\Model\ComposerMessage $message, $account=false) {
		$html = $params['content_type'] == 'html';

		$fullDays = GO::t("full_days");
		
		$replyTo = $message->reply_to->count() ? $message->reply_to : $message->from;
		
		if(!isset($params['alias_id']))
			$params['alias_id']=0;
		
		$recipients = new \GO\Base\Mail\EmailRecipients();
		$recipients->mergeWith($message->cc)->mergeWith($message->to);
		
		$alias = $this->_findAliasFromRecipients($account, $recipients, $params['alias_id']);	
		
		if(empty($params['account_id']) || $alias->account_id != $params['account_id']){
			$templateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($alias->account->id);
			if (!$templateModel)
				$templateModel = \GO\Email\Model\DefaultTemplate::model()->findByPk(\GO::user()->id);

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
			if($message instanceof \GO\Email\Model\ImapMessage)
				$message->createTempFilesForAttachments(true);

			$oldMessage = $message->toOutputArray(true,false,true);
			
			if(!empty($oldMessage['smime_encrypted'])) {
				$oldMessage['htmlbody'] = '***';
			}
			
			
			$AccountModel =  Account::model()->findByPk($params['account_id']);
			if($AccountModel->full_reply_headers) {

				$headerLines = $this->_getFollowUpHeaders($message);
				$header = '<br /><br />' . GO::t("--- Original message follows ---", "email") . '<br />';
				foreach ($headerLines as $line)
					$header .= '<b>' . $line[0] . ':&nbsp;</b>' . htmlspecialchars($line[1], ENT_QUOTES, 'UTF-8') . "<br />";

				$header .= "<br /><br />";
				
				$replyText = $header;


			} else {
				$replyText = sprintf(GO::t("On %s, %s at %s %s wrote:", "email"), $fullDays[date('w', $message->udate)], date(GO::user()->completeDateFormat, $message->udate), date(GO::user()->time_format, $message->udate), $fromArr['personal']);
				
			}
			
			$response['data']['htmlbody'] .= '<br /><br />' .
								$replyText. //htmlspecialchars($replyText, ENT_QUOTES, 'UTF-8') .
								'<br />' . $this->_quoteHtml($oldMessage['htmlbody']);

			// Fix for array_merge function on line below when the $response['data']['inlineAttachments'] do not exist
			if(empty($response['data']['inlineAttachments']))
				$response['data']['inlineAttachments'] = array();

			$response['data']['inlineAttachments'] = array_merge($response['data']['inlineAttachments'], $oldMessage['inlineAttachments']);
		} else {
			
			$AccountModel =  Account::model()->findByPk($params['account_id']);
			if($AccountModel->full_reply_headers) {
				$headerLines = $this->_getFollowUpHeaders($message);
				$replyText = "\n\n" . GO::t("--- Original message follows ---", "email") . "\n";
				foreach ($headerLines as $line)
					$replyText .= $line[0] . ': ' . $line[1] . "\n";
				$replyText .= "\n\n";
			}else
			{
				$replyText = sprintf(GO::t("On %s, %s at %s %s wrote:", "email"), $fullDays[date('w', $message->udate)], date(GO::user()->completeDateFormat, $message->udate), date(GO::user()->time_format, $message->udate), $fromArr['personal']);
			}
			
			$oldMessage = $message->toOutputArray(false,false,true);
			
			if(!empty($oldMessage['smime_encrypted'])) {
				$oldMessage['plainbody'] = '***';
			}
			
			$response['data']['plainbody'] .= "\n\n" . $replyText . "\n" . $this->_quoteText($oldMessage['plainbody']);
		}

		//will be set at send action
//		$response['data']['in_reply_to'] = $message->message_id;

		if (stripos($message->subject, 'Re:') === false) {
			$response['data']['subject'] = 'Re: ' . $message->subject;
		} else {
			$response['data']['subject'] = $message->subject;
		}
		
		if(isset($params['includeAttachments'])){
			// Include attachments

			if($message instanceof \GO\Email\Model\ImapMessage){
				//saved messages always create temp files
				$message->createTempFilesForAttachments();
			}

			$oldMessage = $message->toOutputArray($html,false,true);

			// Fix for array_merge functions on lines below when the $response['data']['inlineAttachments'] and $response['data']['attachments'] do not exist
			if(empty($response['data']['inlineAttachments']))
				$response['data']['inlineAttachments'] = array();

			if(empty($response['data']['attachments']))
				$response['data']['attachments'] = array();

			$response['data']['inlineAttachments'] = array_merge($response['data']['inlineAttachments'], $oldMessage['inlineAttachments']);
			$response['data']['attachments'] = array_merge($response['data']['attachments'], $oldMessage['attachments']);
		}

		if(empty($params['keepHeaders'])){
			

			if (!empty($params['replyAll'])) {
				$toList = new \GO\Base\Mail\EmailRecipients();
				$toList->mergeWith($replyTo)
								->mergeWith($message->to);			

				//remove our own alias from the recipients.		
				if($toList->count()>1){
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
		if($message instanceof \GO\Email\Model\ImapMessage){
			$response['sendParams']['reply_uid'] = $message->uid;
			$response['sendParams']['reply_mailbox'] = $params['mailbox'];
			$response['sendParams']['reply_account_id'] = $params['account_id'];
			$response['sendParams']['in_reply_to'] = $message->message_id;

			//We need to link the contact if a manual link was made of the message to the sender.
			//Otherwise the new sent message may not be linked if an autolink tag is not present.
			if(false && GO::modules()->savemailas){

				$from = $message->from->getAddress();

				$contact = \GO\Addressbook\Model\Contact::model()->findSingleByEmail($from['email'], \GO\Base\Db\FindParams::newInstance()->permissionLevel(Acl::WRITE_PERMISSION));
				if($contact){


					$linkedMessage = \GO\Savemailas\Model\LinkedEmail::model()->findByImapMessage($message, $contact);


					if($linkedMessage && $linkedMessage->linkExists($contact)){

						$tag = $this->_createAutoLinkTag($account, "GO\Addressbook\Model\Contact", $contact->id);


						if($html){
							if(strpos($response['data']['htmlbody'], $tag)===false){
								$response['data']['htmlbody'].= '<div style="display:none">'.$tag.'</div>';
							}
						}else{
							if(strpos($response['data']['plainbody'], $tag)===false){
								$response['data']['plainbody'].= "\n\n".$tag."\n\n";
							}
						}
					}
//						$response['data']['link_text']=$contact->name;
//						$response['data']['link_value']=$contact->className().':'.$contact->id;

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
	 * @return \GO\Email\Model\Alias|false 
	 */
	private function _findAliasFromRecipients($account, \GO\Base\Mail\EmailRecipients $recipients, $alias_id=0, $allAvailableAliases=false){
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
		$stmt = !$allAvailableAliases && $account && $account->checkPermissionLevel(Acl::CREATE_PERMISSION) ? $account->aliases : \GO\Email\Model\Alias::model()->find($findParams);
		while($possibleAlias = $stmt->fetch()){

			if(!$defaultAlias)
				$defaultAlias = $possibleAlias;

			if($recipients->hasRecipient($possibleAlias->email)){
				$alias = $possibleAlias;
				break;
			}
		}

		if(!$alias)
			$alias = empty($alias_id)  ? $defaultAlias : \GO\Email\Model\Alias::model()->findByPk($alias_id);

		return $alias;
	}
	
	/**
	 * Forward a mail message. It can handle an IMAP message or a saved message.
	 *
	 * @param type $params
	 * @return type
	 */
	protected function actionForward($params){

		if(!empty($params['uid'])){
			$account = Account::model()->findByPk($params['account_id']);
			$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
		}else
		{
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeFile($params['path'], !empty($params['is_tmp_file']) && $params['is_tmp_file']!='false');
		}

		return $this->_messageToForwardResponse($params, $message);
	}

	private function _messageToForwardResponse($params, \GO\Email\Model\ComposerMessage $message) {

		$response = $this->loadTemplate($params);

		$html = $params['content_type'] == 'html';

		if (stripos($message->subject, 'Fwd:') === false) {
			$response['data']['subject'] = 'Fwd: ' . $message->subject;
		} else {
			$response['data']['subject'] = $message->subject;
		}

		$headerLines = $this->_getFollowUpHeaders($message);

		if($message instanceof \GO\Email\Model\ImapMessage){
			//saved messages always create temp files
			$message->createTempFilesForAttachments();
		}

		$oldMessage = $message->toOutputArray($html,false,true);

		// Fix for array_merge functions on lines below when the $response['data']['inlineAttachments'] and $response['data']['attachments'] do not exist
		if(empty($response['data']['inlineAttachments']))
			$response['data']['inlineAttachments'] = array();

		if(empty($response['data']['attachments']))
			$response['data']['attachments'] = array();

		$response['data']['inlineAttachments'] = array_merge($response['data']['inlineAttachments'], $oldMessage['inlineAttachments']);
		$response['data']['attachments'] = array_merge($response['data']['attachments'], $oldMessage['attachments']);


		if ($html) {
			$header = '<br /><br />' . GO::t("--- Original message follows ---", "email") . '<br />';
			foreach ($headerLines as $line)
				$header .= '<b>' . $line[0] . ':&nbsp;</b>' . htmlspecialchars($line[1], ENT_QUOTES, 'UTF-8') . "<br />";

			$header .= "<br /><br />";

			$response['data']['htmlbody'] .= $header . $oldMessage['htmlbody'];
		} else {
			$header = "\n\n" . GO::t("--- Original message follows ---", "email") . "\n";
			foreach ($headerLines as $line)
				$header .= $line[0] . ': ' . $line[1] . "\n";
			$header .= "\n\n";

			$response['data']['plainbody'] .= $header . $oldMessage['plainbody'];
		}

		if($message instanceof \GO\Email\Model\ImapMessage){
			//for saving sent items in actionSend
			$response['sendParams']['forward_uid'] = $message->uid;
			$response['sendParams']['forward_mailbox'] = $params['mailbox'];
			$response['sendParams']['forward_account_id'] = $params['account_id'];
		}

		$this->_keepHeaders($response, $params);

		return $response;
	}

	private function _getFollowUpHeaders(\GO\Email\Model\ComposerMessage $message) {

		$lines = array();

		$lines[] = array(GO::t("Subject", "email"), $message->subject);
		$lines[] = array(GO::t("From", "email"), (string) $message->from);
		$lines[] = array(GO::t("To", "email"), (string) $message->to);
		if ($message->cc->count())
			$lines[] = array("CC", (string) $message->cc);

		$lines[] = array(GO::t("Date"), \GO\Base\Util\Date::get_timestamp($message->udate));

		return $lines;
	}

	public function actionView($params) {

//		Do not close session writing because SMIME stores the password in the session
//		GO::session()->closeWriting();

		$params['no_max_body_size'] = !empty($params['no_max_body_size']) && $params['no_max_body_size']!=='false' ? true : false;

		$account = Account::model()->findByPk($params['account_id']);
		if(!$account)
			throw new \GO\Base\Exception\NotFound();

		$imapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);

		if(!$imapMessage)
			throw new \GO\Base\Exception\NotFound();

		//workaround for gmail. It doesn't flag messages as seen automatically.
//		if (!$imapMessage->seen && stripos($account->host, 'gmail') !== false)
//			$imapMessage->getImapConnection()->set_message_flag(array($imapMessage->uid), "\Seen");

		if(!empty($params['create_temporary_attachments']))
			$imapMessage->createTempFilesForAttachments();

		$plaintext = !empty($params['plaintext']);

		$response = $imapMessage->toOutputArray(!$plaintext,false,$params['no_max_body_size']);
		$response['uid'] = intval($params['uid']);
		$response['mailbox'] = $params['mailbox'];
		$response['account_id'] = intval($params['account_id']);
		$response['do_not_mark_as_read'] = $account->do_not_mark_as_read;

		if(!$plaintext){

			if($params['mailbox']!=$account->sent && $params['mailbox']!=$account->drafts) {
				$response = $this->_blockImages($params, $response);
				$response = $this->_checkXSS($params, $response);
			}

			//Don't do these special actions in the special folders
			if($params['mailbox']!=$account->sent && $params['mailbox']!=$account->trash && $params['mailbox']!=$account->drafts){
				$linkedModels = $this->_handleAutoLinkTag($imapMessage, $response);
				$response = $this->_handleInvitations($imapMessage, $params, $response);

				$linkedModels = $this->_handleAutoContactLinkFromSender($imapMessage, $linkedModels);				

			}
			
		}
		
		$response['isInSpamFolder']=$this->_getSpamMoveMailboxName($params['uid'],$params['mailbox'],$account->id);
		$response = $this->_getContactInfo($imapMessage, $params, $response);

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
	
	
	protected function _getSpamMoveMailboxName($mailUid,$mailboxName,$accountId) {
		
		if (strtolower($mailboxName)=='spam') {
			//return '<div class="em-spam-move-block">'.\GO::t("This message has been identified as spam. Click", "email").' <a style="color:blue;" href="javascript:GO.email.moveToInbox(\''.$mailUid.'\','.$accountId.');">'.\GO::t("here", "email").'</a> '.\GO::t("if you think this message is NOT spam.", "email").'</div>';
			return 1;
		} else {
			//return '<div class="em-spam-move-block">'.\GO::t("Click", "email").' <a style="color:blue;" href="javascript:GO.email.moveToSpam(\''.$mailUid.'\',\''.$mailboxName.'\','.$accountId.');">'.\GO::t("here", "email").'</a> '.\GO::t("if you think this message is spam.", "email").'</div>';
			return 0;
		}
		
	}
	
	
	protected function actionGet($account_id, $mailbox, $uid, $query=""){
		return array(
				'success'=>true, 
				'data'=>array(
						'message'=>array(
								'attributes'=>$this->actionView(array('account_id'=>$account_id, 'mailbox'=>$mailbox, 'uid'=>$uid, 'query'=>$query))
								)
						)
				);
	}
	
	protected function actionDelete(){
		return array(
				'success'=>true
		);
	}

	private function _getContactInfo(\GO\Email\Model\ImapMessage $imapMessage,$params, $response){
		$response['sender_contact_id']=0;
		$response['sender_company_id']=0;
		$response['allow_quicklink']=1;
		$response['contact_name']="";
		$response['contact_thumb_url']=null; //GO::config()->host.'modules/addressbook/themes/Default/images/unknown-person.png';

		$useQL = GO::config()->allow_quicklink;
		$response['allow_quicklink']=$useQL?1:0;

		
		$contact = !empty($response['sender']) ? \go\modules\community\addressbook\model\Contact::find(['id', 'photoBlobId', 'isOrganization', 'name', 'addressBookId', 'color'])->filter(['email' => $response['sender'], 'permissionLevel' => \go\core\model\Acl::LEVEL_READ])->single() : false;
		if(!empty($contact)){
			$response['contact_thumb_url']= go()->getAuthState()->getDownloadUrl($contact->photoBlobId);
			$response['contact'] = $contact->toArray();

			if($useQL){
				$response['sender_contact_id']=$contact->id;
				$response['contact_name']=$contact->name;

				$orgIds = $contact->getOrganizationIds();
				

				$company = isset($orgIds[0]) ? \go\modules\community\addressbook\model\Contact::findById($orgIds[0],['id', 'name', 'addressBookId']) : null;
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

	private function _checkXSS($params, $response) {

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

	private function _handleInvitations(\GO\Email\Model\ImapMessage $imapMessage, $params, $response) {

		if(!GO::modules()->isInstalled('calendar'))
			return $response;

		$vcalendar = $imapMessage->getInvitationVcalendar();
		if($vcalendar){
			$vevent = $vcalendar->vevent[0];

			$aliases = GO\Email\Model\Alias::model()->find(
				GO\Base\Db\FindParams::newInstance()
					->select('email')
					->criteria(GO\Base\Db\FindCriteria::newInstance()->addCondition('account_id' , $imapMessage->account->id))
			)->fetchAll(\PDO::FETCH_COLUMN, 0);

			$emailFound = false;
			foreach($vevent->attendee as $vattendee) {
				$attendeeEmail = str_replace('mailto:','', strtolower((string) $vattendee));
				if(in_array($attendeeEmail, $aliases)) {
					$emailFound = true;
					$accountEmail = $attendeeEmail;
				}
			}

			if(!$emailFound) {
				$response['iCalendar']['feedback'] = GO::t("None of the participants match your e-mail aliases for this e-mail account.", "email");
				return $response;
			}

			//is this an update for a specific recurrence?
			$recurrenceDate = isset($vevent->{"recurrence-id"}) ? $vevent->{"recurrence-id"}->getDateTime()->format('U') : 0;

			//find existing event
			$event = \GO\Calendar\Model\Event::model()->findByUuid((string) $vevent->uid, $imapMessage->account->user_id, $recurrenceDate);
//			var_dump($event);

			$uuid = (string) $vevent->uid;

			$alreadyProcessed = false;
			if($event){

				//import to check if there are relevant updates
				$event->importVObject($vevent, array(), true);
				$alreadyProcessed = false; //!$event->isModified($event->getRelevantMeetingAttributes());
//				throw new \Exception(\GO\Base\Util\Date::get_timestamp($vevent->{"last-modified"}->getDateTime()->format('U')).' < '.\GO\Base\Util\Date::get_timestamp($event->mtime));
				
//				if($vevent->{"last-modified"}) {
//					$alreadyProcessed=$vevent->{"last-modified"}->getDateTime()->format('U')<=$event->mtime && $event->is_organizer;
//				}else
//				{
//					$alreadyProcessed=!$event->isModified($event->getRelevantMeetingAttributes());
//				}
			}

//			if(!$event || $event->is_organizer){
				switch($vcalendar->method){
					case 'CANCEL':
						$response['iCalendar']['feedback'] = GO::t("This message contains an event cancellation.", "email");
						break;

					case 'REPLY':
						$response['iCalendar']['feedback'] = GO::t("This message contains an update to an event.", "email");
						break;

					case 'REQUEST':
						$response['iCalendar']['feedback'] = GO::t("This message contains an invitation to an event.", "email");
						break;
				}

				if($vcalendar->method!='REQUEST' && $vcalendar->method!='PUBLISH' && !$event){
					$response['iCalendar']['feedback'] = GO::t("The appointment of this message was deleted.", "email");
				}

				$response['iCalendar']['invitation'] = array(
						'uuid' => $uuid,
						'email_sender' => $response['sender'],
						'email' => $accountEmail,
						//'event_declined' => $event && $event->status == 'DECLINED',
						'event_id' => $event ? $event->id : 0,
						'is_organizer'=>$event && $event->is_organizer,
						'is_processed'=>$alreadyProcessed,
						'is_update' => !$alreadyProcessed && $vcalendar->method == 'REPLY',// || ($vcalendar->method == 'REQUEST' && $event),
						'is_invitation' => !$alreadyProcessed && $vcalendar->method == 'REQUEST', //&& !$event,
						'is_cancellation' => $vcalendar->method == 'CANCEL'
				);
//			}elseif($event){

//			if($event){
//				$response['attendance_event_id']=$event->id;
//			}
//			$subject = (string) $vevent->summary;
			if(empty($uuid) || strpos($response['htmlbody'], $uuid)===false){
				//if(!$event){
					$event = new \GO\Calendar\Model\Event();
					try{
						$event->importVObject($vevent, array(), true);
					//}

					$response['htmlbody'].= '<div style="border: 1px solid black;margin-top:10px">'.
									'<div style="font-weight:bold;margin:2px;">'.GO::t("Attached appointment information", "email").'</div>'.
									$event->toHtml().
									'</div>';
					}
					catch(\Exception $e){
						//$response['htmlbody'].= '<div style="border: 1px solid black;margin-top:10px">Could not render event</div>';
					}
			}
		}

		return $response;
	}

	private function _findAutoLinkTags($data, $account_id=0){
		preg_match_all('/\[link:([^]]+)\]/',$data, $matches, PREG_SET_ORDER);

		$tags = array();
		$unique=array();
		while($match=array_shift($matches)){

			$match[1] = strip_tags($match[1]);
			$match[1] = preg_replace('/\s+/', '',$match[1]);
			$match[1] = preg_replace('/&.+;/', '',$match[1]);
			
			//make sure we don't parse the same tag twice.
			if(!in_array($match[1], $unique)){				
				$props = explode(',',base64_decode($match[1]));
				if($props[0]==$_SERVER['SERVER_NAME'] && count($props) == 4){
					$tag=array();
					
					if(!$account_id || $account_id==$props[1]){
	//				$tag['server'] = $props[0];

						$tag['account_id'] = $props[1];
						$tag['model'] = $props[2];
						$tag['model_id'] = $props[3];

						$tags[]=$tag;
					}
				}

				$unique[]=$match[1];
			}

		}
		return $tags;
	}

	/**
	 * Finds an autolink tag inserted by Group-Office and links the message to the model
	 *
	 * @param \GO\Email\Model\ImapMessage $imapMessage
	 * @param type $params
	 * @param StringHelper $response
	 * @return StringHelper
	 */
	private function _handleAutoLinkTag(\GO\Email\Model\ImapMessage $imapMessage, $response) {
		//seen flag is expensive because it can't be recovered from cache
//		if(!$imapMessage->seen){


		$linkedModels = new \go\core\util\ArrayObject();
		

		if(GO::modules()->savemailas){
			$tags = $this->_findAutoLinkTags($response['htmlbody'], $imapMessage->account->id);

			
			if(!isset($response['autolink_items']))
				$response['autolink_items'] = array();

			while($tag = array_shift($tags)){
				
//				if($imapMessage->account->id == $tag['account_id']){
					try{
						$linkModel = \GO\Savemailas\SavemailasModule::getLinkModel($tag['model'], $tag['model_id']);

						if($linkModel && !$linkedModels->findKeyBy(function($i) use($linkModel) { return $linkModel->equals($i); })){
							
							\GO\Savemailas\Model\LinkedEmail::model()->createFromImapMessage($imapMessage, $linkModel);

							$linkedModels[]=$linkModel;
						}
					}
					catch(\Exception $e){
						\go\core\ErrorHandler::logException($e);
					}
			}
		}

		return $linkedModels;
	}


	/**
	 * When automatic contact linking is enabled this will link received messages to the sender in the addressbook
	 *
	 * @param \GO\Email\Model\ImapMessage $imapMessage
	 * @param type $params
	 * @param StringHelper $response
	 * @return StringHelper
	 */
	private function _handleAutoContactLinkFromSender(\GO\Email\Model\ImapMessage $imapMessage, $linkedModels) {
//todo system setting
		if(GO::modules()->addressbook && GO::modules()->savemailas && Settings::get()->autoLinkEmail){

			$from = $imapMessage->from->getAddress();

			
			$contacts = Contact::findByEmail($from['email'], ['id'])->filter(['permissionLevel' => GoAcl::LEVEL_WRITE]);

			foreach($contacts as $contact) {
				if($contact && $linkedModels->findKeyBy(function($item) use ($contact) { return $item->equals($contact); } ) === false){						
					\GO\Savemailas\Model\LinkedEmail::model()->createFromImapMessage($imapMessage, $contact);
					$linkedModels[]=$contact;					
				}
			}
		}

		return $linkedModels;
	}


	/**
	 * Block external images if sender is not in addressbook.
	 *
	 * @param type $params
	 * @param type $response
	 * @return type
	 */
	private function _blockImages($params, $response) {
		if (empty($params['unblock'])){// && !\GO\Addressbook\Model\Contact::model()->findSingleByEmail($response['sender'])) {
			$blockUrl = 'about:blank';
			$response['htmlbody'] = preg_replace("/<([^a]{1})([^>]*)(https?:[^>'\"]*)/iu", "<$1$2" . $blockUrl, $response['htmlbody'], -1, $response['blocked_images']);
		}

		return $response;
	}

	//still used?
	public function actionMessageAttachment($params){

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

	private function _tnefAttachment($params, Account  $account){

		$tmpFolder = \GO\Base\Fs\Folder::tempFolder(uniqid(time()));
		$tmpFile = $tmpFolder->createChild('winmail.dat');

		$imap = $account->openImapConnection($params['mailbox']);

		$success = $imap->save_to_file($params['uid'], $tmpFile->path(), $params['number'], $params['encoding']);
		if(!$success)
			throw new \Exception("Could not save temp file for tnef extraction");

		chdir($tmpFolder->path());
		exec(GO::config()->cmd_tnef.' '.$tmpFile->path(), $output, $retVar);
		if($retVar!=0)
			throw new \Exception("TNEF extraction failed: ".implode("\n", $output));		
		$tmpFile->delete();

		$items = $tmpFolder->ls();
		if(!count($items)){
			$this->render("Plain",GO::t("This winmail attachment does not contain any files.", "email"));
			exit();
		}

		exec(GO::config()->cmd_zip.' -r "winmail.zip" *', $output, $retVar);
		if($retVar!=0)
			throw new \Exception("ZIP compression failed: ".implode("\n", $output));
		
		$zipFile = $tmpFolder->child('winmail.zip');
		\GO\Base\Util\Http::outputDownloadHeaders($zipFile,false,true);
		$zipFile->output();

		$tmpFolder->delete();
	}

	public function actionAttachment($params) {

		GO::session()->closeWriting();

		$file = new \GO\Base\Fs\File('/dummypath/'.$params['filename']);

		$account = Account::model()->findByPk($params['account_id']);
		//$imapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);

//		if($file->extension()=='dat')
		if(strtolower($file->name()) == 'winmail.dat'){
			return $this->_tnefAttachment ($params, $account);
		}
		
		$inline = true;

		if(isset($params['inline']) && $params['inline'] == 0)
			$inline = false;

		//to work around office bug: http://support.microsoft.com/kb/2019105/en-us
		//never use inline on IE with office documents because it will prompt for authentication.
		$officeExtensions = array('doc','dot','docx','dotx','docm','dotm','xls','xlt','xla','xlsx','xltx','xlsm','xltm','xlam','xlsb','ppt','pot','pps','ppa','pptx','potx','ppsx','ppam','pptm','potm','ppsm');
		if(\GO\Base\Util\Http::isInternetExplorer() && in_array($file->extension(), $officeExtensions)){
			$inline=false;
		}
		
		$imap = $account->openImapConnection($params['mailbox']);
		
		\GO\Base\Util\Http::outputDownloadHeaders($file,$inline,true);
		$fp =fopen("php://output",'w');
		$imap->get_message_part_decoded($params['uid'], $params['number'], $params['encoding'], false, true, false, $fp);
		fclose($fp);


	}

//	Z-push testing
//	public function actionAttachment($uid, $number, $encoding, $account_id, $mailbox, $filename){
//
//		$file = new \GO\Base\Fs\File($filename);
//		\GO\Base\Util\Http::outputDownloadHeaders($file,true,true);
//
//		$account = Account::model()->findByPk($account_id);
//		$imap = $account->openImapConnection($mailbox);
//		include_once('modules/z-push2/backend/go/GoImapStreamWrapper.php');
//
//		$fp = GoImapStreamWrapper::Open($imap, $uid, $number, $encoding);
//
//		while($line = fgets($fp)){
//			echo $line;
//		}
//	}


	protected function actionTnefAttachmentFromTempFile($params){
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


	protected function actionSaveAttachment($params){
		$folder = \GO\Files\Model\Folder::model()->findByPk($params['folder_id']);

		if(!$folder){
			trigger_error("GO\Email\Controller\Message::actionSaveAttachment(".$params['folder_id'].") folder not found", E_USER_WARNING);
			throw new \GO\Base\Exception\NotFound("Specified folder not found");
		}
		
		if(!$folder->checkPermissionLevel(\GO\Base\Model\Acl::WRITE_PERMISSION)) {
			throw new \GO\Base\Exception\AccessDenied();
		}

		$params['filename'] = \GO\Base\Fs\File::stripInvalidChars($params['filename']);		
		$file = new \GO\Base\Fs\File(GO::config()->file_storage_path.$folder->path.'/'.$params['filename']);

		if(empty($params['tmp_file'])){
			$account = Account::model()->findByPk($params['account_id']);
			$imap = $account->openImapConnection($params['mailbox']);
			$response['success'] = $imap->save_to_file($params['uid'], $file->path(), $params['number'], $params['encoding'], true);
		}else
		{
			$tmpfile = new \GO\Base\Fs\File(GO::config()->tmpdir.$params['tmp_file']);
			$file = $tmpfile->copy($file->parent(), $params['filename']);
			$response['success'] = $file != false;
		}
		
		if(!$folder->hasFile($file->name()))
			$folder->addFile($file->name());

		if(!$response['success'])
			$response['feedback']='Could not save to '.$file->stripFileStoragePath();
		return $response;
	}
	
	/**
	 * Save all attachments of the given message to the given folder
	 * 
	 * @param int $folder_id		The id of the folder to save the attachments to
	 * @param int $account_id		The account id of the mailbox account
	 * @param string $mailbox		The affected mailbox in where to search the message uid
	 * @param int $uid					The uid of the message to search in the mailbox
	 * 
	 * @return string						Json string if the request was successfully done
	 * 
	 * @throws \GO\Base\Exception\NotFound
	 * @throws AccessDenied
	 */
	protected function actionSaveAllAttachments($folder_id,$account_id,$mailbox,$uid, $filepath = null){
		$response = array('success'=>true);
		
		$folder = \GO\Files\Model\Folder::model()->findByPk($folder_id);

		if(!$folder){
			trigger_error("GO\Email\Controller\Message::actionSaveAllAttachments(".$folder_id.") folder not found", E_USER_WARNING);
			throw new \GO\Base\Exception\NotFound("Specified folder not found");
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
				throw new \GO\Base\Exception\NotFound("Specified account not found");
			}

			$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $mailbox, $uid);
		}
		if(!$message){
			trigger_error("GO\Email\Controller\Message::actionSaveAllAttachments(". $mailbox." - ". $uid.") message not found", E_USER_WARNING);
			throw new \GO\Base\Exception\NotFound("Specified message could not be found");
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

	protected function actionSource($params) {

		$account = Account::model()->findByPk($params['account_id']);
		$imap  = $account->openImapConnection($params['mailbox']);

		//$filename = empty($params['download']) ? "message.txt" :"message.eml";
		
		$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params['mailbox'], $params['uid']);
		
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
		while ($line = $imap->get_message_part_line())
			echo $line;
	}

	protected function actionMoveOld($params){

		$this->checkRequiredParameters(array('mailbox','target_mailbox'), $params);

		if($params['mailbox']==$params['target_mailbox'])
		{
			throw new \Exception(GO::t("Source and target mailbox may not be the same", "email"));
		}

		$account = Account::model()->findByPk($params['account_id']);
		$imap  = $account->openImapConnection($params['mailbox']);


		$before_timestamp = \GO\Base\Util\Date::to_unixtime($params['until_date']);
		if (empty($before_timestamp))
			throw new \Exception(GO::t("I tried to process the following \"Until Date\", but the processing stopped because an error occurred", "email").': '.$params['until_date']);

		$date_string = date('d-M-Y',$before_timestamp);

		$uids = $imap->sort_mailbox('ARRIVAL',false,'BEFORE "'.$date_string.'"');

		$response['total']=count($uids);
		//$response['success'] = $imap->delete($uids);
		$response['success'] =true;
		if($response['total']){
			$chunks = array_chunk($uids, 1000);
			while($uids=array_shift($chunks)){
				if(!$imap->move($uids, $params['target_mailbox'])){
					throw new \Exception("Could not move mails! ".$imap->last_error());
				}
			}
		}



		return $response;
	}
//
//	protected function moveOld($params){
//		$account = Account::model()->findByPk($params['account_id']);
//		$imap  = $account->openImapConnection($params['mailbox']);
//
//
//		$before_timestamp = \GO\Base\Util\Date::to_unixtime($params['until_date']);
//		if (empty($before_timestamp))
//			throw new \Exception(GO::t("I tried to process the following \"Until Date\", but the processing stopped because an error occurred", "email").': '.$params['until_date']);
//
//		$date_string = date('d-M-Y',$before_timestamp);
//
//		$uids = $imap->sort_mailbox('ARRIVAL',false,'BEFORE "'.$date_string.'"');
//
//		$response['total']=count($uids);
//		$response['success'] = $imap->move($uids, $params['target_mailbox']);
//
//		return $response;
//	}

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
	protected function actionMove($params){
			$start_time = time();

			$messages= json_decode($params['messages'], true);
			$total = $params['total'];

			//move to another imap account
			//$imap2 = new cached_imap();
			//$from_account = $imap->open_account($params['from_account_id'], $params['from_mailbox']);
			$from_account=Account::model()->findByPk($params['from_account_id']);
			$to_account=Account::model()->findByPk($params['to_account_id']);

			if(!$from_account->checkPermissionLevel(Acl::CREATE_PERMISSION))
			  throw new \GO\Base\Exception\AccessDenied();

			if(!$to_account->checkPermissionLevel(Acl::CREATE_PERMISSION))
			  throw new \GO\Base\Exception\AccessDenied();

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
					$flags .= ' $Forwarded';				}

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

			$response['success']=true;

			return $response;
	}

	protected function actionZipAllAttachments($params){

		$account = Account::model()->findByPk($params['account_id']);
		//$imap  = $account->openImapConnection($params['mailbox']);

		$message = \GO\Email\Model\ImapMessage::model()->findByUid($account, $params["mailbox"], $params["uid"]);

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

	protected function actionMoveToSpam($params) {
		
		$accountModel = \GO\Email\Model\Account::model()->findByPk($params['account_id']);
				
		$imap = $accountModel->openImapConnection($params['from_mailbox_name']);
		
		$spamFolder = isset(GO::config()->spam_folder) ? GO::config()->spam_folder : 'Spam';
		
		if(!$imap->get_status($spamFolder)){
			$imap->create_folder($spamFolder);
		}
							
		if (!$imap->move(array($params['mail_uid']), $spamFolder)) {
			$imap->disconnect();
			throw new \Exception('Could not move message to "'.$spamFolder.'" folder. Does it exist?');
		}
		
		$response = array('success'=>true);
		echo json_encode($response);
		
	}
	
	protected function actionMoveToInbox($params) {
		
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
