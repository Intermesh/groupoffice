<?php

echo "Updating SSL status of Email accounts ....\n\n";

$allEmailAccountsWithSSLEnabled = \GO\Email\Model\Account::model()->findByAttribute('deprecated_use_ssl',true, \GO\Base\Db\FindParams::newInstance()->ignoreAcl());

foreach($allEmailAccountsWithSSLEnabled as $account){
	echo sprintf("Updating SSL settings for account with id: %s.\n",$account->id);
	$account->imap_encryption = 'ssl';
	$account->checkImapConnectionOnSave = false;
	if(!$account->save()){
		echo sprintf("The account with id: %s gives an error while saving the new SSL properties, please check this manually.\n",$account->id);
	}
}
