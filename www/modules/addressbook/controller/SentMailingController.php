<?php


namespace GO\Addressbook\Controller;


class SentMailingController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Addressbook\Model\SentMailing';
	
	/**
	 * Disable email sending
	 * 
	 * @var boolean 
	 */
	protected $dry=false;

	protected function allowGuests() {
		return array("batchsend","unsubscribe");
	}
	
	protected function ignoreAclPermissions() {
		return array('unsubscribe');
	}
	
	/**
	 * This function is made specially to convert paramaters from the EmailComposer
	 * to match \GO\Base\Mail\Message::handleFormInput in actionSendMailing.
	 * @param Array $params Parameters from EmailComposer
	 * @return Array $params Parameters for \GO\Base\Mail\Message::handleFormInput 
	 */
//	private function _convertOldParams($params) {
//		$params['inlineAttachments'] = json_decode($params['inline_attachments']);
//
//		foreach ($params['inlineAttachments'] as $k => $ia) {
//			// tmpdir part may already be at the beginning of $ia['tmp_file']
//			if (strpos($ia->tmp_file, \GO::config()->tmpdir) == 0)
//				$ia->tmp_file = substr($ia->tmp_file, strlen(\GO::config()->tmpdir));
//
//			$params['inlineAttachments'][$k] = $ia;
//		}
//		$params['inlineAttachments'] = json_encode($params['inlineAttachments']);
//
//		if (!empty($params['content_type']) && strcmp($params['content_type'], 'html') != 0)
//			$params['body'] = $params['textbody'];
//
//		// Replace "[id:" string part in subject by the actual alias id
//		if (!empty($params['alias_id']) && !empty($params['subject']))
//			$params['subject'] = str_replace('[id:', '[' . $params['alias_id'] . ':', $params['subject']);
//
//		return $params;
//	}


	protected function actionSend($params) {
		if (empty($params['addresslist_id'])) {
			throw new \Exception(\GO::t("You didn't enter a recipient", "email"));
		} else {
			try {
				//$params = $this->_convertOldParams($params);

				if (
					\GO::modules()->isAvailable('campaigns') && isset($params['campaign_id']) && $params['campaign_id']>0
					&&
					(
						empty(\GO::config()->campaigns_imap_user) || empty(\GO::config()->campaigns_imap_pass)
						|| empty(\GO::config()->campaigns_imap_server) || empty(\GO::config()->campaigns_imap_port)
						|| !isset(\GO::config()->campaigns_smtp_user) || !isset(\GO::config()->campaigns_smtp_pass)
						|| empty(\GO::config()->campaigns_smtp_server) || empty(\GO::config()->campaigns_smtp_port)
						|| empty(\GO::config()->campaigns_from) || empty(\GO::config()->campaigns_max_mails_per_period)
					)
				) {
					throw new \Exception(\GO::t("Could not send the campaigns mailing because one or more of the following config.php settings were not set:<br />\$config['campaigns_imap_user'], \$config['campaigns_imap_pass'], \$config['campaigns_imap_server'], \$config['campaigns_imap_port'],<br />\$config['campaigns_smtp_user'], \$config['campaigns_smtp_pass'], \$config['campaigns_smtp_server'], \$config['campaigns_smtp_port'],<br />\$config['campaigns_from'], \$config['campaigns_max_mails_per_period'].", "campaigns"));
				}
					
				$message = \GO\Base\Mail\Message::newInstance();
				$message->handleEmailFormInput($params); // insert the inline and regular attachments in the MIME message

				$mailing['alias_id'] = $params['alias_id'];
				$mailing['subject'] = $params['subject'];
				$mailing['addresslist_id'] = $params['addresslist_id'];
				$mailing['campaign_id'] = $params['campaign_id'];
				$mailing['message_path'] =  'mailings/' . \GO::user()->id . '_' . date('Ymd_Gis') . '.eml';

				$folder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'mailings');
				$folder->create();

				// Write message MIME source to message path
				file_put_contents(\GO::config()->file_storage_path.$mailing['message_path'], $message->toString());

				\GO::debug('===== MAILING PARAMS =====');
				\GO::debug(var_export($mailing,true));

				$sentMailing = new \GO\Addressbook\Model\SentMailing();
				$sentMailing->setAttributes($mailing);
				if (!$sentMailing->save()) {
								\GO::debug('===== VALIDATION ERRORS =====');
								\GO::debug('Could not create new mailing:<br />'.implode('<br />',$sentMailing->getValidationErrors()));
								throw new \Exception('Could not create new mailing:<br />'.implode('<br />',$sentMailing->getValidationErrors()).'<br />MAILING PARAMS:<br />'.var_export($mailing,true));
				}       

				$this->_launchBatchSend($sentMailing->id);

				$response['success'] = true;
			} catch (\Exception $e) {
				$response['feedback'] = \GO::t("There was an unexpected problem building the email: ", "email") . $e->getMessage();
			}
		}
		return $response;
	}

	private function _launchBatchSend($mailing_id) {
		
		$mailing = \GO\Addressbook\Model\SentMailing::model()->findByPk($mailing_id);
		if (!$mailing)
			throw new \Exception("Mailing not found!\n");

		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()->addRawCondition('t.id', 'a.account_id');
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single()
						->join(\GO\Email\Model\Alias::model()->tableName(), $joinCriteria, 'a')
						->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('id', $mailing->alias_id, '=', 'a')
		);
		
		$account = \GO\Email\Model\Account::model()->find($findParams);
		
		$pwdParam = '';

		if($account  && !empty(\GO::session()->values['emailModule']['smtpPasswords'][$account->id])){
			$mailing->temp_pass = \GO::session()->values['emailModule']['smtpPasswords'][$account->id];
			$mailing->save();
			
			$pwdParam = '--smtp_password='.\GO::session()->values['emailModule']['smtpPasswords'][$account->id];
		}
				
		$log = \GO::config()->file_storage_path . 'log/mailings/';
		if (!is_dir($log))
			mkdir($log, 0755, true);

		$log .= $mailing_id . '.log';
		$cmd = \GO::config()->cmd_php . ' '.\GO::config()->root_path.'groupofficecli.php -r=addressbook/sentMailing/batchSend -c="' . \GO::config()->get_config_file() . '" --mailing_id=' . $mailing_id . ' '.$pwdParam.' >> ' . $log;

		if (!\GO\Base\Util\Common::isWindows())
			$cmd .= ' 2>&1 &';

		file_put_contents($log, \GO\Base\Util\Date::get_timestamp(time()) . "\r\n" . $cmd . "\r\n\r\n", FILE_APPEND);
		if (\GO\Base\Util\Common::isWindows()) {
			pclose(popen("start /B " . $cmd, "r"));
		} else {
			exec($cmd,$outputarr,$returnvar);
			\GO::debug('===== CMD =====');
			\GO::debug($cmd);
			\GO::debug('===== OUTPUT ARR =====');
			\GO::debug(var_export($outputarr,true));
			\GO::debug('===== RETURN VAR =====');
			\GO::debug(var_export($returnvar,true));
		}
	}
	
	private $_sentEmails;

	protected function actionBatchSend($params) {

		$this->requireCli();
		
		$this->_sentEmails=array();
		
		\GO::$disableModelCache=true;

		$mailing = \GO\Addressbook\Model\SentMailing::model()->findByPk($params['mailing_id'], false, true);
		if (!$mailing)
			throw new \Exception("Mailing not found!\n");

		\GO::session()->runAs($mailing->user_id);
		
		echo 'Status: '.$mailing->status."\n";
		
		if(empty($mailing->status)){
			echo "Starting mailing at ".\GO\Base\Util\Date::get_timestamp(time())."\n";
			$mailing->reset();
			
		}else if($mailing->status== \GO\Addressbook\Model\SentMailing::STATUS_RUNNING) {
			echo 'Is already running!\n';
			exit();
		}else if($mailing->status== \GO\Addressbook\Model\SentMailing::STATUS_WAIT_PAUSED) {
			
			// Do error!!!
			
			echo "It is still running ".\GO\Base\Util\Date::get_timestamp(time())."\n";
			exit();
			
		}elseif (!empty($params['restart'])) {
			echo "Restarting mailing at ".\GO\Base\Util\Date::get_timestamp(time())."\n";
			$mailing->reset();
		}elseif($mailing->status==\GO\Addressbook\Model\SentMailing::STATUS_PAUSED){
			echo "Resuming mailing at ".\GO\Base\Util\Date::get_timestamp(time())."\n";
			$mailing->status=\GO\Addressbook\Model\SentMailing::STATUS_RUNNING;
			$mailing->save();
		}
			
		$htmlToText = new \GO\Base\Util\Html2Text();
		

		//$addresslist = \GO\Addressbook\Model\Addresslist::model()->findByPk($mailing->addresslist_id);
		$mimeData = file_get_contents(\GO::config()->file_storage_path .$mailing->message_path);
		$message = \GO\Base\Mail\Message::newInstance()
						->loadMimeMessage($mimeData);


		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()->addRawCondition('t.id', 'a.account_id');
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single()
						->join(\GO\Email\Model\Alias::model()->tableName(), $joinCriteria, 'a')
						->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('id', $mailing->alias_id, '=', 'a')
		);

		if ($mailing->campaign_id>0 && \GO::modules()->isAvailable('campaigns')) {
			$account = new \GO\Email\Model\Account();
			
			$account->username = \GO::config()->campaigns_imap_user;
			$account->password = \GO::config()->campaigns_imap_pass;
			$account->host = \GO::config()->campaigns_imap_server;
			$account->port = \GO::config()->campaigns_imap_port;
			$account->smtp_username = \GO::config()->campaigns_smtp_user;
			$account->smtp_password = \GO::config()->campaigns_smtp_pass;
			$account->smtp_host = \GO::config()->campaigns_smtp_server;
			$account->smtp_port = \GO::config()->campaigns_smtp_port;			
			$message->setFrom(\GO::config()->campaigns_from);
			
		} else {
			$account = \GO\Email\Model\Account::model()->find($findParams);
			
			if(!$account->store_password && !empty($mailing->temp_pass)){
				$account->smtp_password = $mailing->temp_pass;
			}	
		}
		
		$mailer = \GO\Base\Mail\Mailer::newGoInstance(\GO\Email\Transport::newGoInstance($account));

		echo "Will send emails from " . $account->username . ".\n";
		
		if(empty(\GO::config()->mailing_messages_per_minute))
			\GO::config()->mailing_messages_per_minute=5;

		//Rate limit to 100 emails per-minute
		$mailer->registerPlugin(new \Swift_Plugins_ThrottlerPlugin(\GO::config()->mailing_messages_per_minute, \Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE));
		
		// Use AntiFlood to re-connect after 50 emails
		$mailer->registerPlugin(new \Swift_Plugins_AntiFloodPlugin(\GO::config()->mailing_messages_per_minute));

		echo 'Sending a maximum of ' . \GO::config()->mailing_messages_per_minute . ' messages per minute' . "\n";

		$failedRecipients = array();

		$bodyWithTags=$message->getBody();

		foreach ($mailing->contacts as $contact) {			
			
			$sentMailingContactModel = \GO\Addressbook\Model\SentMailingContact::model()
				->findSingleByAttributes(array('sent_mailing_id'=>$mailing->id,'contact_id'=>$contact->id));
			
			if (!$sentMailingContactModel->sent) {
			
				$errors=1;

				$unsubscribeHref=\GO::url('addressbook/sentMailing/unsubscribe', 
								array(
										'addresslist_id'=>$mailing->addresslist_id, 
										'contact_id'=>$contact->id, 
										'token'=>md5($contact->ctime.$contact->addressbook_id.$contact->firstEmail) //token to check so that users can't unsubscribe other members by guessing id's
										), false, true, false);

				$body = str_replace('%unsubscribe_href%', $unsubscribeHref, $bodyWithTags); //curly brackets don't work inside links in browser wysiwyg editors.

				$templateModel = \GO\Addressbook\Model\Template::model();
				$templateModel->htmlSpecialChars = false;
				$body = $templateModel->replaceCustomTags($body,array(				
					'unsubscribe_link'=>'<a href="'.$unsubscribeHref.'" target="_blank">'.\GO::t("Click here to unsubscribe from this address list.", "addressbook").'</a>'
				), true);
				$templateModel->htmlSpecialChars = true;

				try{
					if(!$contact->email_allowed){
						echo "Skipping contact ".$contact->firstEmail." because newsletter sending is disabled in the addresslists tab.\n\n";
					}elseif(empty($contact->firstEmail)){
						echo "Skipping contact ".$contact->name." no e-mail address was set.\n\n";					
					}else
					{		
						$body = \GO\Addressbook\Model\Template::model()->replaceContactTags($body, $contact);
						$message->setTo($contact->firstEmail, $contact->name);
						$message->setBody($body);

						$plainTextPart = $message->findPlainTextBody();
						if($plainTextPart){
							$htmlToText->set_html($body);
							$plainTextPart->setBody($htmlToText->get_text());
						}

						// Check mail limit
						$nSentMails = \GO::config()->get_setting('campaigns_number_sent_mails',0);
						if ($mailing->campaign_id>0 && $nSentMails>=\GO::config()->campaigns_max_mails_per_period) {
							$this->_pauseMailing($mailing->id);
							echo "Error for ".$contact->firstEmail.": \n";
							echo str_replace('%maxMails',\GO::config()->campaigns_max_mails_per_period,\GO::t("Maximum number of campaign emails for the current period has been reached. The limit is: %maxMails. This mailing has been paused.", "campaigns"));
							exit();
						}

						$this->_sendmail($message, $contact, $mailer, $mailing);

						\GO::config()->save_setting('campaigns_number_sent_mails', $nSentMails+1, 0);
						$errors=0;

					}
				}catch(\Exception $e){
					echo "Error for ".$contact->firstEmail.": ".$e->getMessage()."\n";
				}

				if($errors){
					$mailing->errors++;
					$mailing->save();
				}
			
			}
			
		}

		foreach ($mailing->companies as $company) {
			
			$sentMailingCompanyModel = \GO\Addressbook\Model\SentMailingCompany::model()
				->findSingleByAttributes(array('sent_mailing_id'=>$mailing->id,'company_id'=>$company->id));
			
			if (!$sentMailingCompanyModel->sent) {
			
				$errors=1;

				$unsubscribeHref=\GO::url('addressbook/sentMailing/unsubscribe', 
								array(
										'addresslist_id'=>$mailing->addresslist_id, 
										'company_id'=>$company->id, 
										'token'=>md5($company->ctime.$company->addressbook_id.$company->email) //token to check so that users can't unsubscribe other members by guessing id's
										), true, true);

				$body = str_replace('%unsubscribe_href%', $unsubscribeHref, $bodyWithTags); //curly brackets don't work inside links in browser wysiwyg editors.

				$body = \GO\Addressbook\Model\Template::model()->replaceCustomTags($body,array(				
					'unsubscribe_link'=>'<a href="'.$unsubscribeHref.'">'.\GO::t("Click here to unsubscribe from this address list.", "addressbook").'</a>'
				), true);

				try{
					if(!$company->email_allowed){
						echo "Skipping company ".$company->email." because newsletter sending is disabled in the addresslists tab.\n\n";
					}elseif(empty($company->email)){
						echo "Skipping company ".$company->name." no e-mail address was set.\n\n";
					}else
					{		
						$body = \GO\Addressbook\Model\Template::model()->replaceModelTags($body, $company);
						$message->setTo($company->email, $company->name);
						$message->setBody($body);

						$plainTextPart = $message->findPlainTextBody();
						if($plainTextPart){
							$htmlToText->set_html($body);
							$plainTextPart->setBody($htmlToText->get_text());
						}

						// Check mail limit
						$nSentMails = \GO::config()->get_setting('campaigns_number_sent_mails',0);
						if ($mailing->campaign_id>0 && $nSentMails>=\GO::config()->campaigns_max_mails_per_period) {
							$this->_pauseMailing($mailing->id);
							echo "Error for ".$contact->firstEmail.": \n";
							echo str_replace('%maxMails',\GO::config()->campaigns_max_mails_per_period,\GO::t("Maximum number of campaign emails for the current period has been reached. The limit is: %maxMails. This mailing has been paused.", "campaigns"));
							exit();
						}

						$this->_sendmail($message, $company, $mailer, $mailing);	

						\GO::config()->save_setting('campaigns_number_sent_mails', $nSentMails+1, 0);
						$errors=0;
					}

				}catch(\Exception $e){
					echo "Error for ".$company->email.": ".$e->getMessage()."\n";
				}

				if($errors){
					$mailing->errors++;
					$mailing->save();
				}
			
			}
			
		}

		$mailing->status = \GO\Addressbook\Model\SentMailing::STATUS_FINISHED;
		
		// Unset the temp_pass
		if(!empty($mailing->temp_pass)){
			$mailing->temp_pass = "";
		}
		
		$mailing->save();

		echo "Mailing finished at ".\GO\Base\Util\Date::get_timestamp(time())."\n";
	}
	
	public function actionUnsubscribe($params){
		
		if(!isset($params['contact_id']))
			$params['contact_id']=0;
		
		if(!isset($params['company_id']))
			$params['company_id']=0;
		
		if(!empty($params['sure'])){
			if($params['contact_id']){
				$contact = \GO\Addressbook\Model\Contact::model()->findByPk($params['contact_id']);
				
				if(md5($contact->ctime.$contact->addressbook_id.$contact->firstEmail) != $params['token'])
					throw new \Exception("Invalid token!");
				
				$contact->email_allowed=0;
				$contact->save();					
				
				\GO\Base\Mail\AdminNotifier::sendMail("Unsubscribe: ".$contact->email, "Contact ".$contact->email. " unsubscribed from receiving newsletters");
			}else
			{
				if($params['contact_id']){
					$company = \GO\Addressbook\Model\Company::model()->findByPk($params['company_id']);

					if(md5($company->ctime.$company->addressbook_id.$company->email) != $params['token'])
						throw new \Exception("Invalid token!");

					$company->email_allowed=0;
					$company->save();
					
					\GO\Base\Mail\AdminNotifier::sendMail("Unsubscribe: ".$company->email, "Company ".$contact->email. " unsubscribed from receiving newsletters");
				}
			}
			
			$this->render('unsubscribed', $params);
		}else
		{		
			$this->render('unsubscribe',$params);
		}
	}
	
	private $smtpFailCount=0;

	private function _sendmail($message, $model, $mailer, $mailing) {
		
		$typestring = $model instanceof \GO\Addressbook\Model\Company ? 'company' : 'contact';
		
		if($typestring=='contact'){
			$email = $model->firstEmail;
		}else
		{
			$email = $model->email;
		}		
		
		echo '['.\GO\Base\Util\Date::get_timestamp(time())."] Sending to " . $typestring . " id: " . $model->id . " email: " . $email . "\n";

		if ($typestring=='contact')
			$sentMailModel = \GO\Addressbook\Model\SentMailingContact::model()->findSingleByAttributes(array('sent_mailing_id'=>$mailing->id,'contact_id'=>$model->id));
		else
			$sentMailModel = \GO\Addressbook\Model\SentMailingCompany::model()->findSingleByAttributes(array('sent_mailing_id'=>$mailing->id,'company_id'=>$model->id));
		
		
		$mailing = \GO\Addressbook\Model\SentMailing::model()->findByPk($mailing->id, array(), true, true);
		
		
		if($mailing->status==\GO\Addressbook\Model\SentMailing::STATUS_WAIT_PAUSED)
		{
			$mailing->status = \GO\Addressbook\Model\SentMailing::STATUS_PAUSED;
			$mailing->save();
			echo "Mailing paused by user. Exiting.";
			exit();
		}

		try {
			if(in_array($email, $this->_sentEmails)){
				echo "Skipping because this e-mail address already got an e-mail\n";
			}elseif($this->dry){
				echo "Not sending because dry is true\n";
			}else{
				$this->fireEvent('beforeMessageSend',array(&$message,$model,$mailing));
				$this->_sentEmails[]=$email;
				$mailer->send($message);
			}
		} catch (\Exception $e) {
			$status = $e->getMessage();
		}
		if (!empty($status)) {
			$errorMsg = "---------\n".
				"Failed at ".\GO\Base\Util\Date::get_timestamp(time())."\n".
				$status . "\n".
				"---------\n";
			
			echo $errorMsg;
			
//			$mailing->errors++;		
			$sentMailModel->has_error = true;
			$sentMailModel->error_description = $errorMsg;
			
			$this->smtpFailCount++;
			
			if($this->smtpFailCount==3){
				echo "Pausing mailing because there were 3 send errors in a row\n";
				$mailing->status=\GO\Addressbook\Model\SentMailing::STATUS_PAUSED;
				$mailing->save();
				exit();				
			}
			
			unset($status);
		} else {
			$sentMailModel->sent = true;
//			$mailing->sent++;
			$this->smtpFailCount=0;
		}
		
		$sentMailModel->save();
//		$mailing->save();

//		if ($typestring == 'contact') {
//			$mailing->removeManyMany('contacts', $model->id);
//		} else {
//			$mailing->removeManyMany('companies', $model->id);			
//		}
		
		
	}
	
	protected function getStoreParams($params) {
		
		$criteria = \GO\Base\Db\FindCriteria::newInstance();
		
		
		$criteria->addCondition('campaign_id',$params['campaign_id']);
		
		return \GO\Base\Db\FindParams::newInstance()->criteria($criteria);
						
	}

	private function _pauseMailing($mailingId) {
		$mailing = \GO\Addressbook\Model\SentMailing::model()->findByPk($mailingId);
		if($mailing->status==\GO\Addressbook\Model\SentMailing::STATUS_RUNNING){
			$mailing->status = \GO\Addressbook\Model\SentMailing::STATUS_WAIT_PAUSED;
			$mailing->save();
		}
	}
	
	protected function beforeStore(&$response, &$params, &$store) {

		if (!empty($params['pause_mailing_id'])) {
			$this->_pauseMailing($params['pause_mailing_id']);
		}

		if (!empty($params['start_mailing_id'])) {
			$this->_launchBatchSend($params['start_mailing_id']);
		}

		$store->setDefaultSortOrder('ctime', 'DESC');
		return $response;
	}

	public function formatStoreRecord($record, $model, $store) {
		$record['hide_pause'] = in_array($model->status, array(\GO\Addressbook\Model\SentMailing::STATUS_PAUSED, \GO\Addressbook\Model\SentMailing::STATUS_FINISHED, \GO\Addressbook\Model\SentMailing::STATUS_WAIT_PAUSED));
		$record['hide_play'] = in_array($model->status, array(\GO\Addressbook\Model\SentMailing::STATUS_RUNNING, \GO\Addressbook\Model\SentMailing::STATUS_FINISHED, \GO\Addressbook\Model\SentMailing::STATUS_WAIT_PAUSED));
		$record['addresslist'] = !empty($model->addresslist) ? $model->addresslist->name : '';
		$record['user_name'] = !empty($model->user) ? $model->user->name : '';
		return parent::formatStoreRecord($record, $model, $store);
	}
	
	protected function actionViewLog($params){
		$mailing = \GO\Addressbook\Model\SentMailing::model()->findByPk($params['mailing_id']);
		
		if($mailing->user_id != \GO::user()->id && !\GO::user()->isAdmin())
			throw new \GO\Base\Exception\AccessDenied();				
		
		$file = $mailing->logFile;		
		\GO\Base\Util\Http::outputDownloadHeaders($file);
		$file->output();
	}

}
