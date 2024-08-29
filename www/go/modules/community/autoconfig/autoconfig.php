<?php
// Set autoconfig CNAME record to the server running /mail/config-v1.1.xml
use go\core\App;
require("../../../../vendor/autoload.php");
App::get();
header('Content-Type: application/xml');



if(go()->getModule("community", "maildomains")) {
	$imapHost = go\modules\community\maildomains\Module::get()->getSettings()->getMailHost();
} else {
	$email = $_GET['emailaddress'] ?? null;
	if(isset($email) && go()->getModule("legacy", "email") && ($account = \GO\Email\Model\Account::model()->findByEmail($email))) {
		$imapHost = $account->host;
	} else {
		$imapHost = go()->getSettings()->smtpHost;
	}
}
?><?xml version="1.0"?>
<clientConfig version="1.1">
	<emailProvider id="<?= $imapHost ?>">
		<domain>%EMAILDOMAIN%</domain>
		<displayName>Group-Office Mail service</displayName>
		<displayShortName>Group-Office email</displayShortName>
		<incomingServer type="imap">
			<hostname><?= $imapHost ?></hostname>
			<port>993</port>
			<socketType>SSL</socketType>
			<authentication>password-cleartext</authentication>
			<username>%EMAILADDRESS%</username>
		</incomingServer>
		<outgoingServer type="smtp">
			<hostname><?= $imapHost ?></hostname>
			<port>587</port>
			<socketType>STARTTLS</socketType>
			<username>%EMAILADDRESS%</username>
			<authentication>password-cleartext</authentication>
		</outgoingServer>
	</emailProvider>

    <webMail>
        <loginPage url="<?= go()->getSettings()->URL; ?>" />
    </webMail>
</clientConfig>