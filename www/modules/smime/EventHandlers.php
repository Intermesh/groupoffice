<?php


namespace GO\Smime;

use GO;


class EventHandlers {

	public static function loadAccount(\GO\Email\Controller\AccountController $controller, &$response, \GO\Email\Model\Account $account, $params) {
		$cert = (new Model\Smime($account->id))->latestCert();
		$settings = Model\AccountSettings::model()->createOrFindByParams(['account_id'=>$account->id]);
		if ($cert && !empty($cert->isValid())) {
			$response['data']['cert'] = true;
			$response['data']['always_sign'] = $settings->always_sign;
		}
	}

	public static function submitAccount(\GO\Email\Controller\AccountController $controller, &$response, \GO\Email\Model\Account $account, $params, $modifiedAttributes)
	{
		$settings = Model\AccountSettings::model()->createOrFindByParams(['account_id'=>$account->id]);
		$settings->account_id = $account->id;
		$settings->always_sign = !empty($params['always_sign']) ? 1 : 0;
		$settings->save();
	}

	public static function aliasesStore(\GO\Email\Controller\AliasController $controller, &$response, \GO\Base\Data\Store $store, $params) {

		foreach ($response['results'] as &$alias) {

			$accountModel = \GO\Email\Model\Account::model()->findByPk($alias['account_id']);
			$settings = Model\AccountSettings::model()->findByPk($alias['account_id']);

			if ($settings && !empty($accountModel)) {
				$alias['has_smime_cert'] = true;
				$alias['always_sign'] = $settings->always_sign;
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
		
		if ($imapMessage->content_type != 'application/pkcs7-mime') {
			return;
		}

		$encrypted = !isset($imapMessage->content_type_attributes['smime-type']) || ($imapMessage->content_type_attributes['smime-type'] != 'signed-data');
		if (!$encrypted) {
			return;
		}

		GO::debug("Message is encrypted");

		$date = date('Y-m-d H:i:s', $imapMessage->udate);

		$cert = (new GO\Smime\Model\Smime($imapMessage->account->id))->latestCert($date);

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

		$password = GO::session()->values['smime']['passwords'][$imapMessage->account->id];
		$infile = \GO\Base\Fs\File::tempFile();

		if(!$imapMessage->saveToFile($infile->path()))
			throw new \Exception("Could not save IMAP message to file for decryption");

		$outfile = $cert->setPassword($password)->decryptFile($infile);

		if(is_string($outfile)) { // failed decrypting
			$response['htmlbody'] = $outfile;
			return;
		}

		//check if also signed
		$data = $outfile->getContents();
		if (strpos($data, 'signed-data')) {
			$verifyOutfile = \GO\Base\Fs\File::tempFile();
			openssl_pkcs7_verify($outfile->path(), null, "/dev/null", array(), GO::config()->root_path . "modules/smime/dummycert.pem", $verifyOutfile->path());

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

		foreach ($newResponse as $key => $value) {
			if (!empty($value) || $key == 'attachments')
				$response[$key] = $value;
		}
		$response['smime_encrypted'] = true;
		//$response['path']=$outfile->stripTempPath();

		$outfile->delete();


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

	}

	public static function beforeSend(\GO\Email\Controller\MessageController $controller, array &$response, \GO\Base\Mail\SmimeMessage $message, \GO\Base\Mail\Mailer $mailer, \GO\Email\Model\Account $account, \GO\Email\Model\Alias $alias, $params) {

		if(!empty($params['sign_smime']) || !empty($params['encrypt_smime'])) {
			$password = GO::session()->values['smime']['passwords'][$account->id];
			$cert = (new GO\Smime\Model\Smime($account->id))->latestCert();
		}

		if (!empty($params['sign_smime'])) {
			$message->setSignParams($cert->cert, $password);
		}

		if (!empty($params['encrypt_smime'])) {
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
