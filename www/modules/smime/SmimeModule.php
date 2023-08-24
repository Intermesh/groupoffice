<?php

namespace GO\Smime;


use Exception;
use go\core\exception\Unauthorized;

class SmimeModule extends \GO\Base\Module{
	public static function initListeners() {
		$accountController = new \GO\Email\Controller\AccountController();
		$accountController->addListener('load', "GO\Smime\EventHandlers", "loadAccount");
		$accountController->addListener('submit', "GO\Smime\EventHandlers", "submitAccount");

		$messageController = new \GO\Email\Controller\MessageController();
		$messageController->addListener('beforesend', "GO\Smime\EventHandlers", "beforeSend");
		
		\GO\Email\Model\ImapMessage::model()->addListener('tooutputarray', "GO\Smime\EventHandlers", "toOutputArray");
		
		$aliasController = new \GO\Email\Controller\AliasController();
		$aliasController->addListener('store', "GO\Smime\EventHandlers", "aliasesStore");

		\GO\Base\Model\User::model()->addListener('delete', "GO\Smime\SmimeModule", "deleteUser");
		
	}
	
	public static function deleteUser($user) {		
		Model\PublicCertificate::model()->deleteByAttribute('user_id', $user->id);
	}


	/**
	 * Read certificates from a PKCS12 file
	 *
	 * @param string $data
	 * @param string $passphrase
	 * @return string[]
	 * @throws Exception
	 */
	public static function readPKCS12(string $data, string $passphrase): array
	{
		openssl_pkcs12_read($data, $certs, $passphrase);
		if(!$certs) {
			$error =  openssl_error_string();
			if(str_contains($error, "11800071")) {
				throw new Unauthorized(go()->t("The SMIME password was incorrect.",  "legacy", "smime"));
			} else {
				throw new Exception(go()->t("Could not read p12 file:", "legacy", "smime").' ' .$error);
			}
		}

		return $certs;
	}
}
