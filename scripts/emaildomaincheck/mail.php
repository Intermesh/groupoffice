<?php
//this was used in 6.2
require ('/usr/share/groupoffice/GO.php');

$records = json_decode(file_get_contents("result.json"), true);

foreach($records as $record) {
	if($record['allPassed']) {
		continue;
	}

	$body = "Dear customer,
	
Your mail domain ".$record['mailDomain']." is configured on our mailserver. We have detected that the domain settings 
are not optimal. To ensure the best delivery of your mail please make these DNS changes:

";

	if(!$record['dmarc']) {
		$body .= "Add a DMARC DNS record of type TXT for _dmarc.".$record['mailDomain']." with the value:\n\nv=DMARC1; p=quarantine;\n\n";
	}

	if(!$record['spf']) {
		$body .= "Add an SPF DNS record of type TXT for ".$record['mailDomain']." with value:\n\nv=spf1 a:smtp.groupoffice.net a:smtp.group-office.com ip4:149.210.188.96 ip4:149.210.243.200 -all\n\n";
	}

	if(!$record['dkim']) {
		$body .= "You don't have a DKIM record. Please add this record for DKIM:";

		$output = [];

		exec("/usr/local/bin/opendkim-genkey.sh ".$record['mailDomain'], $output);

		$body .= implode("\n", $output);
	}

	foreach($record['mxTargets'] as $t) {
		if($t == 'mx1.imfoss.nl') {
			$body .= "Your MX is set to an old domain mx1.imfoss.nl that we are phasing out. Please set the MX record to: smtp.group-office.com.\n\n";
		}
	}

	$body .= "If you need help configuring the DNS please contact your domain administrator for support.
	
Best regards,

Merijn Schering
Intermesh
	
	";



	$account = \GO\Postfixadmin\Model\Mailbox::model()->findSingleByAttributes(['username' => 'info@'.$record['mailDomain'], "active" => true]);

	if(!$account) {
		$domain = \GO\Postfixadmin\Model\Domain::model()->findSingleByAttribute('domain', $record['mailDomain']);
		if(!$domain) {
			echo "No mailbox found for ". $record['mailDomain'];
			continue;
		}
		$account = \GO\Postfixadmin\Model\Mailbox::model()->findSingleByAttributes(['domain_id' => $domain->id, "active" => true]);
		if(!$account) {
			echo "No mailbox found for ". $record['mailDomain'];
			continue;
		}
	}

//	echo $msg;

	$msg = new \GO\Base\Mail\Message();
	$msg
		->setSubject("Optimise your email deliverability")
		->setFrom("mschering@intermesh.nl", "Merijn Schering (Intermesh)")
		->setBcc("mschering@intermesh.nl")
		->setBody($body, "text/plain")
		->setTo("mschering@intermesh.nl");//$account->username);

	\GO\Base\Mail\Mailer::newGoInstance()->send($msg);


	echo "\n\n\n\n\n=========\n\n\n\n\n";
}