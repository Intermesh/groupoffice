<?php
//UPDATE `go_modules` SET `version` = '92' WHERE `go_modules`.`id` = 'email';

$accounts = \GO\Email\Model\Account::model()->find();

foreach($accounts as $account) {	
	if(strpos($account->password, "{GOCRYPT2}") === 0) {
		return;
	}
	
	echo "Re encrypting password for account ".$account->id ."\n";
	
	try {
		$account->checkImapConnectionOnSave = false;
		
		if(substr($account->password, 0, 9) != "{GOCRYPT}") {
			//plain text
			$pass = $account->password;
			$account->password = "---";
			$account->save();
			$account->password = $pass;
		} else
		{
			$account->password = $account->decryptPassword();
		}
		
		if(substr($account->smtp_password, 0, 9) != "{GOCRYPT}") {
			//plain text
			$pass = $account->smtp_password;
			$account->smtp_password = "---";
			$account->save();
			$account->smtp_password = $pass;
		} else
		{
			$account->smtp_password = $account->decryptSmtpPassword();
		}

		if(!$account->save()) {
			throw new \Exception("Failed to re encrypt password!" . var_export($account->getValidationErrors(), true));
		}
	} catch (\Exception $e) {
		echo $e->getMessage() ."\n";
	}
}
