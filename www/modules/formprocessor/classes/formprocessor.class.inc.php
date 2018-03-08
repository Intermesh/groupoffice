<?php



class formprocessor{
	/*
	 * For spammers...
	 */
	var $no_urls=true;

	var $user_groups=array();
	var $visible_user_groups=array();

	//will be replaced on send of confirmation
	var $confirmation_replacements=array('salutation'=>'Hello','myName'=>'Jan Testpersoon');

	function localize_dbfields($post_fields)
	{
		global $lang, $GO_LANGUAGE;

		require_once($GO_LANGUAGE->get_language_file('addressbook'));

		$fields['name']=\GO::t('name');
		$fields['title']=\GO::t('title');
		$fields['first_name']=\GO::t('firstName');
		$fields['middle_name']=\GO::t('middleName');
		$fields['last_name']=\GO::t('lastName');
		$fields['initials']=\GO::t('initials');
		$fields['sex']=\GO::t('sex');
		$fields['birthday']=\GO::t('birthday');
		$fields['email']=\GO::t('email');
		$fields['country']=\GO::t('country');
		$fields['state']=\GO::t('state');
		$fields['city']=\GO::t('city');
		$fields['zip']=\GO::t('zip');
		$fields['address']=\GO::t('address');
		$fields['address_no']=\GO::t('addressNo');
		$fields['home_phone']=\GO::t('phone');
		$fields['work_phone']=\GO::t('workphone');
		$fields['fax']=\GO::t('fax');
		$fields['work_fax']=\GO::t('workFax');
		$fields['cellular']=\GO::t('cellular');
		$fields['company']=\GO::t('company');
		$fields['department']=\GO::t('department');
		$fields['function']=\GO::t('function');
		$fields['comment']=\GO::t('comment','addressbook');
		$fields['salutation']=\GO::t('salutation');

		$localized = array();
		foreach($post_fields as $key=>$value)
		{
			$newkey = isset($fields[$key]) ? $fields[$key] : $key;
			$localized[$newkey]=$value;
		}
		return $localized;
	}

	function check_required(){
		//remove empty texts		
		if(isset($_POST['empty_texts'])){
			foreach($_POST['empty_texts'] as $value){
				
				$value = explode(':',$value);
				
				$key = $value[0];
				$value=$value[1];
				
				if($pos = strpos($key, '['))
				{
					$key1 = substr($key,0,$pos);
					$key2 = substr($key,$pos+1, -1);

					if(isset($_POST[$key1][$key2]) && $_POST[$key1][$key2]==$value){
						$_POST[$key1][$key2]='';
					}
				}else
				{
					if(isset($_POST[$key]) && $_POST[$key]==$value){
						$_POST[$key]='';
					}
				}
			}
		}
		
		if(isset($_POST['required']))
		{
			foreach($_POST['required'] as $key)
			{
				if($pos = strpos($key, '['))
				{
					$key1 = substr($key,0,$pos);
					$key2 = substr($key,$pos+1, -1);

					if(empty($_POST[$key1][$key2]))
					{
						throw new Exception(\GO::t('missingField'));
					}
				}else
				{
					if(empty($_POST[$key]))
					{
						throw new Exception(\GO::t('missingField'));
					}
				}
			}
		}
	}

	function process_form()
	{
		\GO::$ignoreAclPermissions=true;
		
		$this->check_required();		

		if(!isset($_POST['salutation']))
			$_POST['salutation'] = isset($_POST['sex']) ? \GO::t('default_salutation_'.$_POST['sex']) : \GO::t('default_salutation_unknown');

		//user registation
//		if(!empty($_POST['username'])){
//			$credentials = array ('username','first_name','middle_name','last_name','title','initials','sex','email',
//			'home_phone','fax','cellular','address','address_no',
//			'zip','city','state','country','company','department','function','work_phone',
//			'work_fax');
//
//			if($_POST['password1'] != $_POST['password2'])
//			{
//				throw new Exception(\GO::t('error_match_pass','users'));
//			}
//
//			foreach($credentials as $key)
//			{
//				if(!empty($_REQUEST[$key]))
//				{
//					$userCredentials[$key] = $_REQUEST[$key];
//				}
//			}
//			$userCredentials['password']=$_POST['password1'];
//
//			$userModel = new \GO\Base\Model\User();
//			$userModel->setAttributes($userCredentials);
//			$userModel->save();
//			foreach($this->user_groups as $groupId) {
//				$currentGroupModel = \GO\Base\Model\Group::model()->findByPk($groupId);
//				if($groupId>0 && $groupId!=\GO::config()->group_everyone && !$currentGroupModel->hasUser($userModel->id)) {
//					$currentGroupModel->addUser($userModel->id);
//				}
//			}
//			foreach($this->visible_user_groups as $groupId) {
//				$userAclModel = \GO\Base\Model\Acl::model()->findByPk($userModel->acl_id);
//				if($groupId>0 && !empty($userAclModel) && $userAclModel->hasGroup($groupId)) {
//					$userAclModel->addGroup($groupId);
//				}
//			}
//
//			\GO::session()->login($userCredentials['username'], $userCredentials['password']);
//		}		

		if(!empty($_POST['email']) && !\GO\Base\Util\StringHelper::validate_email($_POST['email']))
		{
			throw new Exception(\GO::t('invalidEmailError'));
		}

		if(!empty($_REQUEST['addressbook']))
		{
//			require($GO_LANGUAGE->get_language_file('addressbook'));
//			require_once($GO_MODULES->modules['addressbook']['class_path'].'addressbook.class.inc.php');
//			$ab = new addressbook();
//
//			$addressbook = $ab->get_addressbook_by_name($_REQUEST['addressbook']);
			$addressbookModel = \GO\Addressbook\Model\Addressbook::model()
				->findSingleByAttribute('name',$_REQUEST['addressbook']);
			if(!$addressbookModel)
			{
				throw new Exception('Addressbook not found!');
			}

			$credentials = array ('first_name','middle_name','last_name','title','initials','sex','email',
			'email2','email3','home_phone','fax','cellular','comment','address','address_no',
			'zip','city','state','country','company','department','function','work_phone',
			'work_fax','salutation','url_linkedin','url_facebook','url_twitter','skype_name');			

			foreach($credentials as $key)
			{
				if(!empty($_REQUEST[$key]))
				{
					$contactCredentials[$key] = $_REQUEST[$key];
				}
			}

			if(isset($contactCredentials['comment']) && is_array($contactCredentials['comment']))
			{
				$comments='';
				foreach($contactCredentials['comment'] as $key=>$value)
				{
					if($value=='date')
					{
						$value = date($_SESSION['GO_SESSION']['date_format'].' '.$_SESSION['GO_SESSION']['time_format']);
					}
					if(!empty($value))
					{
						$comments .= trim($key).":\n".trim($value)."\n\n";
					}
				}
				$contactCredentials['comment']=$comments;
			}


			if($this->no_urls && isset($contactCredentials['comment']) && stripos($contactCredentials['comment'], 'http')){
				throw new Exception('Sorry, but to prevent spamming we don\'t allow URL\'s in the message');
			}

			$contactCredentials['addressbook_id']=$addressbookModel->id;
			$contactCredentials['email_allowed']=isset($_POST['email_allowed']) ? '1' : '0';

				
				

			if(!empty($contactCredentials['company']) && empty($contactCredentials['company_id']))
			{
				$companyModel = \GO\Addressbook\Model\Company::model()
					->findSingleByAttributes(array(
						'name' => $contactCredentials['company'],
						'addressbook_id' => $contactCredentials['addressbook_id']
					));
				if(empty($companyModel))
				{
					$companyModel = new \GO\Addressbook\Model\Company();
					$companyModel->addressbook_id = $contactCredentials['addressbook_id'];
					$companyModel->name = $contactCredentials['company']; // bedrijfsnaam
					$companyModel->user_id = \GO::user()->id;
					$companyModel->save();
					$contactCredentials['company_id'] = $companyModel->id;
				}
			}
			if(isset($_POST['birthday']))
			{
				try {
					$contactCredentials['birthday'] = \GO\Base\Util\Date::to_db_date($_POST['birthday'], false);
				} catch (Exception $e) {
					throw new Exception(\GO::t('birthdayFormatMustBe').': '.$_SESSION['GO_SESSION']['date_format'].'.');
				}
				if(!empty($_POST['birthday']) && $contactCredentials['birthday']=='0000-00-00')
						throw new Exception(\GO::t('invalidDateError'));
			}

			unset($contactCredentials['company']);
				
			$existingContactModel=false;
			if(!empty($_POST['contact_id']))
				$existingContactModel = \GO\Addressbook\Model\Contact::model()->findByPk($_POST['contact_id']);
			elseif(!empty($contactCredentials['email']))
				$existingContactModel = \GO\Addressbook\Model\Contact::model()
							->findSingleByAttributes(array(
								'email' => $contactCredentials['email'],
								'addressbook_id' => $contactCredentials['addressbook_id']
						));
				
			if($existingContactModel)
			{
				$this->contact_id=$contactId = $existingContactModel->id;
				
				$filesFolderId = $existingContactModel->files_folder_id = $existingContactModel->getFilesFolder()->id;
			/*
				* Only update empty fields
				*/
				if(empty($_POST['contact_id']))
					foreach($contactCredentials as $key=>$value)
						if($key!='comment')
							if(!empty($existingContactModel->$key))
								unset($contactCredentials[$key]);

				$contactCredentials['id']=$contactId;

				if(!empty($existingContactModel->comment) && !empty($contactCredentials['comment']))
				$contactCredentials['comment']=$existingContactModel->comment."\n\n----\n\n".$contactCredentials['comment'];

				if(empty($contactCredentials['comment']))
					unset($contactCredentials['comment']);
			
				$existingContactModel->setAttributes($contactCredentials);
				$existingContactModel->save();
				
			} else {
				
				$newContactModel = new \GO\Addressbook\Model\Contact();
				$newContactModel->setAttributes($contactCredentials);			
				$newContactModel->save();
				
				$this->contact_id=$contactId = $newContactModel->id;
				$filesFolderId=$newContactModel->files_folder_id = $newContactModel->getFilesFolder()->id;
				$newContactModel->save();

				if(isset($_POST['contact_id']) && empty($userId) && \GO::user()->id>0)
					$userId=$this->user_id=\GO::user()->id;

				if(!empty($userId)){
					$userModel = \GO\Base\Model\User::model()->findByPk($userId);
					$userModel->contact_id=$contactId;
					$userModel->save();
				}
				
			}
			if(!$contactId)
			{
				throw new Exception(\GO::t('saveError'));
			}

			if ( \GO::modules()->isInstalled('files') )
			{
				$folderModel = \GO\Files\Model\Folder::model()->findByPk($filesFolderId);
				$path = $folderModel->path;

				$response['files_folder_id']=$filesFolderId;

				$full_path = \GO::config()->file_storage_path.$path;				


				foreach($_FILES as $key=>$file)
				{
					if($key!='photo'){//photo is handled later
						if (is_uploaded_file($file['tmp_name']))
						{
							$fsFile = new \GO\Base\Fs\File($file['tmp_name']);
							$fsFile->move(new \GO\Base\Fs\Folder($full_path),$file['name'], false,true);
							$fsFile->setDefaultPermissions();
			
							\GO\Files\Model\File::importFromFilesystem($fsFile);
						}
					}
				}
			}

			if( \GO::modules()->isInstalled('customfields') )
			{
				$cfFields = array();
				foreach ($_POST as $k => $v)
					if (strpos($k,'col_')===0)
						$cfFields[$k]=$v;

				$contactCfModel = \GO\Addressbook\Customfields\Model\Contact::model()->findByPk($contactId);
				if (!$contactCfModel) {
					$contactCfModel = new \GO\Addressbook\Customfields\Model\Contact();
					$contactCfModel->model_id = $contactId;
				}
				$contactCfModel->setAttributes($cfFields);
				$contactCfModel->save();
			}

			if(isset($_POST['mailings'])){
				foreach($_POST['mailings'] as $mailingName)
				{
					if(!empty($mailingName))
					{
						$addresslistModel = \GO\Addressbook\Model\Addresslist::model()->findSingleByAttribute('name', $mailingName);
						if(empty($addresslistModel))
							throw new Exception('Addresslist not found!');
						$addresslistModel->addManyMany('contacts', $contactId);
					}
				}
			}

			if ($this->contact_id > 0) {
				if (isset($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
					$fsFile = new \GO\Base\Fs\File($_FILES['photo']['tmp_name']);
					$fsFile->move(new \GO\Base\Fs\Folder(\GO::config()->tmpdir),$_FILES['photo']['name'], false,false);
					$contactModel = \GO\Addressbook\Model\Contact::model()->findByPk($contactId);
					$contactModel->setPhoto(\GO::config()->tmpdir . $_FILES['photo']['name']);
				}
			}

			if(!isset($_POST['contact_id'])){
				/**
				 * Send notification of new contact to (1) users specified by 'notify_users'
				 * in the form itself and to (2) the addressbook owner if so specified. 
				 */				
				
				// Send the email to the admin users in the language of the addressbook owner.
				$oldLanguage = \GO::language()->getLanguage();
				\GO::language()->setLanguage($addressbookModel->user->language);
				
				$usersToNotify = isset($_POST['notify_users']) ? explode(',', $_POST['notify_users']) : array();
				if(!empty($_POST['notify_addressbook_owner']))
					$usersToNotify[]=$addressbookModel->user_id;
				
				$mailTo = array();
				foreach($usersToNotify as $userToNotifyId)
				{
					$userModel = \GO\Base\Model\User::model()->findByPk($userToNotifyId);
					$mailTo[]=$userModel->email;
				}
				
				if(count($mailTo))
				{			
					$viewContactUrl = \GO::createExternalUrl('addressbook', 'showContact', array($contactId));
					$contactModel = \GO\Addressbook\Model\Contact::model()->findByPk($contactId);
					$companyModel = \GO\Addressbook\Model\Company::model()->findByPk($contactModel->company_id);
					if (!empty($companyModel)) {
						$companyName = $companyModel->name;
					} else {
						$companyName = '';
					}

					$values = array('address_no', 'address', 'zip', 'city', 'state', 'country');
					$formatted_address = nl2br(\GO\Base\Util\Common::formatAddress(
							'{country}', '{address}', '{address_no}', '{zip}', '{city}', '{state}'
						));
					foreach($values as $val)
						$formatted_address = str_replace('{'.$val.'}', $contactModel->$val, $formatted_address);

					$body = \GO::t('newContactFromSite','addressbook').':<br />';
					$body .= \GO::t('name','addressbook').': '.$contactModel->addressbook->name.'<br />';
					$body .= "<br />".$contactModel->name;
					$body .= "<br />".$formatted_address;
					if (!empty($contactModel->home_phone)) $body .= "<br />".\GO::t('phone').': '.$contactModel->home_phone;
					if (!empty($contactModel->cellular)) $body .= "<br />".\GO::t('cellular').': '.$contactModel->cellular;
					if (!empty($companyName)) $body .= "<br /><br />".$companyName;
					if (!empty($contactModel->work_phone)) $body .= "<br />".\GO::t('workphone').': '.$contactModel->work_phone;
					$body .= '<br /><a href="'.$viewContactUrl.'">'.\GO::t('clickHereToView','addressbook').'</a>'."<br />";

					$mailFrom = !empty($_POST['mail_from']) ? $_POST['mail_from'] : \GO::config()->webmaster_email;
					
					$mailMessage = \GO\Base\Mail\Message::newInstance( \GO::t('newContactAdded','addressbook'), $body, 'text/html' )
						->setFrom($mailFrom, \GO::config()->title);
					foreach ($mailTo as $v)
						$mailMessage->addTo($v);		
					\GO\Base\Mail\Mailer::newGoInstance()->send($mailMessage);
				}
				
				// Restore the language
				\GO::language()->setLanguage($oldLanguage);
			}
		
//	
//	
//	Maybe make this workable with GO 4.0 later....
//
//
//			if(isset($_POST['confirmation_template']))
//			{
//				if(empty($_POST['email']))
//				{
//					throw new Exception('Fatal error: No email given for confirmation e-mail!');
//				}
//
//				$url = create_direct_url('addressbook', 'showContact', array($contactId));
//				$body = $lang['addressbook']['newContactFromSite'].'<br /><a href="'.$url.'">'.$lang['addressbook']['clickHereToView'].'</a>';
//
//				global $smarty;
//				$email = $smarty->fetch($_POST['confirmation_template']);
//
//				$pos = strpos($email,"\n");
//
//				$subject = trim(substr($email, 0, $pos));
//				$body = trim(substr($email,$pos));
//
//				require_once(\GO::config()->class_path.'mail/GoSwift.class.inc.php');
//				$swift = new GoSwift($_POST['email'], $subject);
//				$swift->set_body($body);
//				$swift->set_from(\GO::config()->webmaster_email, \GO::config()->title);
//				$swift->sendmail();
//			}

			if(isset($_POST['confirmation_email']) && !empty($_POST['email']))
			{
				if (strpos($_POST['confirmation_email'], '../') !== false || strpos($_POST['confirmation_email'], '..\\')!==false)
					throw new Exception('Invalid path');
				
				$path = \GO::config()->file_storage_path.$_POST['confirmation_email'];
				if(!file_exists($path))
					$path = dirname(\GO::config()->get_config_file()).'/'.$_POST['confirmation_email'];
				//$email = file_get_contents($path);
				
				//$messageModel = \GO\Email\Model\SavedMessage::model()->createFromMimeFile($path);
//				$htmlBodyString = \GO\Addressbook\Model\Template::model()->replaceUserTags($messageModel->getHtmlBody());
//				$htmlBodyString = \GO\Addressbook\Model\Template::model()
//								->replaceContactTags(
//												$htmlBodyString,
//												\GO\Addressbook\Model\Contact::model()->findByPk($contactId),
//												false);
//				$messageModel->body = 
				
				$mailMessage = \GO\Base\Mail\Message::newInstance()->loadMimeMessage(file_get_contents($path));
				$htmlBodyString = $mailMessage->getBody();
				foreach ($this->confirmation_replacements as $tag => $replacement)
					$htmlBodyString = str_replace('{'.$tag.'}',$replacement,$htmlBodyString);				
				$htmlBodyString = \GO\Addressbook\Model\Template::model()->replaceUserTags($htmlBodyString,true);

				$htmlBodyString = \GO\Addressbook\Model\Template::model()
								->replaceContactTags(
												$htmlBodyString,
												\GO\Addressbook\Model\Contact::model()->findByPk($contactId),
												false);

				$mailMessage->setBody($htmlBodyString);
				$mailMessage->setFrom($mailMessage->getFrom(),$mailMessage->getSender());
				$mailMessage->addTo($_POST['email']);
				\GO\Base\Mail\Mailer::newGoInstance()->send($mailMessage);
			}
		}
	}

	function process_simple_contact_form($email, $from_email='', $from_name=''){

		$this->check_required();
		
		if (empty($_POST['email']) || empty($_POST['subject']))
		{
			throw new Exception(\GO::t('missingField'));
		}elseif(!String::validate_email($_POST['email']))
		{
			throw new Exception(\GO::t('invalidEmailError'));
		}

		$body = isset($_POST['body']) ? $_POST['body'] : '';

		if(isset($_POST['extra'])){
			foreach($_POST['extra'] as $name=>$value){
				if(!empty($value))
					$body .= "\n\n".$name.":\n".$value;
			}
		}

		if(empty($from_email))
			$from_email = $_POST['email'];

		if(empty($from_name))
			$from_name = isset($_POST['name']) ? $_POST['name'].' (Via website)' : $from_email;		

		if($this->no_urls && stripos($body, 'http')!==false){
					throw new Exception('Sorry, but to prevent spamming we don\'t allow URL\'s in the message');
				}

		//if(empty($body))
			//throw new Exception(\GO::t(''missingField']);

		require_once(\GO::config()->root_path.'classses/mail/GoSwift.class.inc.php');
		$swift = new GoSwift($email, $_POST['subject']);
		$swift->set_body($body, 'plain');
		$swift->set_from($from_email, $from_name);
		return $swift->sendmail();
	}
}