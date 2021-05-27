<?php


namespace GO\Smime;

use GO;


class EventHandlers {

	public static function loadAccount(\GO\Email\Controller\AccountController $controller, &$response, \GO\Email\Model\Account $account, $params) {
		$cert = Model\Certificate::model()->findByPk($account->id);
		if ($cert && !empty($cert->cert)) {
			$response['data']['cert'] = true;
			$response['data']['always_sign'] = $cert->always_sign;
		}
	}
	
	public static function deleteAccount(\GO\Email\Model\Account $account){
		$cert = Model\Certificate::model()->findByPk($account->id);
		if($cert)
			$cert->delete();		
	}

	public static function submitAccount(\GO\Email\Controller\AccountController $controller, &$response, \GO\Email\Model\Account $account, $params, $modifiedAttributes) {

		if (isset($_FILES['cert']['tmp_name'][0]) && is_uploaded_file($_FILES['cert']['tmp_name'][0])) {
			//check Group-Office password
			if (!GO::user()->checkPassword($params['smime_password']))
				throw new \Exception(GO::t("The Group-Office password was incorrect.", "smime"));

			$certData = file_get_contents($_FILES['cert']['tmp_name'][0]);

			//smime password may not match the Group-Office password
			openssl_pkcs12_read($certData, $certs, $params['smime_password']);
			if (!empty($certs))
				throw new \Exception(GO::t("Your SMIME key password matches your Group-Office password. This is prohibited for security reasons!", "smime"));

			//password may not be empty.
			openssl_pkcs12_read($certData, $certs, "");
			if (!empty($certs))
				throw new \Exception(GO::t("Your SMIME key has no password. This is prohibited for security reasons!", "smime"));
		}

		$cert = Model\Certificate::model()->findByPk($account->id);
		if (!$cert) {
			$cert = new Model\Certificate();
			$cert->account_id = $account->id;
		}

		if (isset($certData))
			$cert->cert = $certData;
		if (!empty($params['delete_cert']) || empty($cert->cert)) {
			//$cert->cert = null;
			$cert->delete();
		} else {
			$cert->always_sign = !empty($params['always_sign']);
			$cert->save();
		}

		if (!empty($cert->cert))
			$response['cert'] = true;
	}

	public static function aliasesStore(\GO\Email\Controller\AliasController $controller, &$response, \GO\Base\Data\Store $store, $params) {

		foreach ($response['results'] as &$alias) {

			$accountModel = \GO\Email\Model\Account::model()->findByPk($alias['account_id']);
			$cert = Model\Certificate::model()->findByPk($alias['account_id']);

			if ($cert && !empty($accountModel)) {
				$alias['has_smime_cert'] = true;
				$alias['always_sign'] = $cert->always_sign;
			}
		}
	}

	public static function toOutputArray(array &$response, \GO\Email\Model\ImapMessage $imapMessage, $html) {
		
		if($imapMessage->content_type == 'application/x-pkcs7-mime')
			$imapMessage->content_type = 'application/pkcs7-mime';
		
		if  ($imapMessage->content_type == 'application/pkcs7-mime' && isset($imapMessage->content_type_attributes['smime-type']) && $imapMessage->content_type_attributes['smime-type']=='signed-data') {
			
			//signed data but not in clear text. Outlook has this option.
			
			$outfile = \GO\Base\Fs\File::tempFile();
			$imapMessage->getImapConnection()->save_to_file($imapMessage->uid, $outfile->path());
			
			$verifyOutfile = \GO\Base\Fs\File::tempFile();

//			$cmd = '/usr/bin/openssl smime -verify -in ' . $outfile->path() . ' -out ' . $verifyOutfile->path();
//			exec($cmd);
//			
			//PHP can't output the verified data without the signature without 
			//suppling the extracerts option. We generated a dummy certificate for 
			//this.
			openssl_pkcs7_verify($outfile->path(), null, "/dev/null", array(), GO::config()->root_path."modules/smime/dummycert.pem", $verifyOutfile->path());

			$mime = $verifyOutfile->getContents();
			$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($mime);
			
			//remove temp files
			$outfile->delete();
			$verifyOutfile->delete();
			unset($mime);

			$newResponse = $message->toOutputArray(true);
			
			unset($newResponse['to']);					
			unset($newResponse['cc']);
					
			foreach ($newResponse as $key => $value) {
				if (!empty($value) || $key == 'attachments')
					$response[$key] = $value;
			}
//			$response['path'] = $outfile->stripTempPath();
			return;
		}
		
		if ($imapMessage->content_type == 'application/pkcs7-mime') {

			$encrypted = !isset($imapMessage->content_type_attributes['smime-type']) || ($imapMessage->content_type_attributes['smime-type'] != 'signed-data');
			if ($encrypted) {

				GO::debug("Message is encrypted");

				$cert = Model\Certificate::model()->findByPk($imapMessage->account->id);

				if (!$cert || empty($cert->cert)) {					
					GO::debug('SMIME: No private key at all found for this account');
					$response['htmlbody'] =GO::t("This message is encrypted and you don't have the private key to decrypt this message.", "smime");
					return false;
				}

				if (isset($_REQUEST['password']))
					GO::session()->values['smime']['passwords'][$imapMessage->account->id] = $_REQUEST['password'];

				if (!isset(GO::session()->values['smime']['passwords'][$imapMessage->account->id])) {
					$response['askPassword'] = true;
					GO::debug("Need to ask for password");
					return false;
				}
			}

			$attachments = $imapMessage->getAttachments();
			$att = array_shift($attachments);
			
			

//			array (
//      'type' => 'application',
//      'subtype' => 'pkcs7-mime',
//      'smime-type' => 'enveloped-data',
//      'name' => 'smime.p7m',
//      'id' => false,
//      'encoding' => 'base64',
//      'size' => '2302',
//      'md5' => false,
//      'disposition' => false,
//      'language' => false,
//      'location' => false,
//      'charset' => false,
//      'lines' => false,
//      'number' => 1,
//      'extension' => 'p7m',
//      'human_size' => '2,2 KB',
//      'tmp_file' => false,
//    )

			$infile = \GO\Base\Fs\File::tempFile();
			$outfile = \GO\Base\Fs\File::tempFile();

			//$outfilerel = $reldir . 'unencrypted.txt';

			if ($encrypted) {
				GO::debug('Message is encrypted');

				
//				$imapMessage->getImapConnection()->save_to_file($imapMessage->uid, $infile->path(), 'TEXT', 'base64');
//				throw new \Exception($infile->path());
								
				if(!$imapMessage->saveToFile($infile->path()))
					throw new \Exception("Could not save IMAP message to file for decryption");
				
				$password = GO::session()->values['smime']['passwords'][$imapMessage->account->id];
				openssl_pkcs12_read($cert->cert, $certs, $password);

				if (empty($certs)) {
					//password invalid
					$response['askPassword'] = true;
					GO::debug("Invalid password");
					return false;
				}

				$return = openssl_pkcs7_decrypt($infile->path(), $outfile->path(), $certs['cert'], array($certs['pkey'], $password));

				$infile->delete();

				if (!$return || !$outfile->exists() || !$outfile->size()) {					
					$response['htmlbody'] = GO::t("SMIME Decryption of this message failed.", "smime") . '<br />';
					while ($str = openssl_error_string()) {
						$response['htmlbody'].='<br />' . $str;
					}
					GO::debug("Decryption failed");
					return false;
				}else
				{
					
					//check if also signed
					$data = $outfile->getContents();
					if(strpos($data, 'signed-data')){
						$verifyOutfile = \GO\Base\Fs\File::tempFile();					
						openssl_pkcs7_verify($outfile->path(), null, "/dev/null", array(), GO::config()->root_path."modules/smime/dummycert.pem", $verifyOutfile->path());
						
						$outfile = $verifyOutfile;
					}					
					
					$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($outfile->getContents());
					$newResponse = $message->toOutputArray($html);
					unset($newResponse['to']);					
					unset($newResponse['to_string']);
					unset($newResponse['cc']);
					unset($newResponse['from']);
					unset($newResponse['full_from']);
					unset($newResponse['sender']);
					
					foreach($newResponse as $key=>$value){
						if(!empty($value) || $key=='attachments')
							$response[$key]=$value;
					}
					$response['smime_encrypted']=true;
					//$response['path']=$outfile->stripTempPath();

					$outfile->delete();
				}
			}else
			{
				GO::debug('Message is NOT encrypted');
			}
		}
	}

	public static function beforeSend(\GO\Email\Controller\MessageController $controller, array &$response, \GO\Base\Mail\SmimeMessage $message, \GO\Base\Mail\Mailer $mailer, \GO\Email\Model\Account $account, \GO\Email\Model\Alias $alias, $params) {
		if (!empty($params['sign_smime'])) {

			//$password = trim(file_get_contents("/home/mschering/password.txt"));
			$password = GO::session()->values['smime']['passwords'][$account->id];

			$cert = Model\Certificate::model()->findByPk($account->id);
			$message->setSignParams($cert->cert, $password);
		}

		if (!empty($params['encrypt_smime'])) {

			if (!isset($cert))
				$cert = Model\Certificate::model()->findByPk($account->id);

			$password = GO::session()->values['smime']['passwords'][$account->id];
			openssl_pkcs12_read($cert->cert, $certs, $password);

			if (!isset($certs['cert']))
				throw new \Exception("Failed to get your public key for encryption");


			$to = $message->getTo();

			$cc = $message->getCc();

			$bcc = $message->getBcc();

			if (is_array($cc))
				$to = array_merge($to, $cc);

			if (is_array($bcc))
				$to = array_merge($to, $bcc);

			//lookup all recipients
			$failed = array();
			$publicCerts = array($certs['cert']);
			foreach ($to as $email => $name) {
				$pubCert = Model\PublicCertificate::model()->findSingleByAttributes(array('user_id' => GO::user()->id, 'email' => $email));
				if (!$pubCert) {
					$failed[] = $email;
				}else
				{
					$publicCerts[] = $pubCert->cert;
				}
			}

			if (count($failed))
				throw new \Exception(sprintf(GO::t("Could not encrypt message because you don't have the public certificate for %s. Open a signed message of the recipient and verify the signature to import the public key.", "smime"), implode(', ', $failed)));

			$message->setEncryptParams($publicCerts);
		}
	}

}
