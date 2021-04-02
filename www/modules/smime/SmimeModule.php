<?php

namespace GO\Smime;


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
		
		\GO\Email\Model\Account::model()->addListener('delete', "GO\Smime\EventHandlers", "deleteAccount");
		
		\GO\Base\Model\User::model()->addListener('delete', "GO\Smime\SmimeModule", "deleteUser");
		
	}
	
	public static function deleteUser($user) {		
		Model\PublicCertificate::model()->deleteByAttribute('user_id', $user->id);
	}
}
