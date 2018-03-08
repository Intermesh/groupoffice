<?php
$accountsStmt = \GO\Email\Model\Account::model()->find();
while ($accountModel = $accountsStmt->fetch()) {
	if (!empty($accountModel->smtp_password)) {
		try{
			// Trick the model this field has been modified, to circumvent
			$pwBuffer = $accountModel->smtp_password;
			$accountModel->smtp_password = "";
			$accountModel->smtp_password = $pwBuffer;
			$accountModel->save();
		}catch(\Exception $e){
			echo $e->getMessage();
		}
	}
}
?>
