<?php
define("GO_CONFIG_FILE", '/etc/groupoffice/multi_instance/intermesh.group-office.com/config.php');
require ('../../www/GO.php');

$records = json_decode(file_get_contents("result.json"), true);

foreach($records as $record) {
	if($record['allPassed']) {
		continue;
	}

	$msg = "Dear customer,
	
Your mail domain ".$record['mailDomain']." is configured on our mailserver. We have detected that the domain settings 
are not optimal. To ensure the best delivery of your mail please make these DNS changes:

";

	if(!$record['dmarc']) {
		$msg .= "Add a DMARC DNS record of type TXT for _dmarc.".$record['mailDomain']." with the value:\n\nv=DMARC1; p=quarantine;\n\n";
	}

	if(!$record['spf']) {
		$msg .= "Add an SPF DNS record of type TXT for ".$record['mailDomain']." with value:\n\nv=spf1 a:smtp.groupoffice.net a:smtp.group-office.com ip4:149.210.188.96 ip4:149.210.243.200 -all\n\n";
	}

	if(!$record['dkim']) {
		$msg .= "You don't have a DKIM record. Please contact us if you like to setup DKIM for your domain. It's not strictly required but highly recommended to setup.\n\n";
	}

	foreach($record['mxTargets'] as $t) {
		if($t == 'mx1.imfoss.nl') {
			$msg .= "Your MX is set to an old domain mx1.imfoss.nl that we are phasing out. Please set the MX record to: smtp.group-office.com.\n\n";
		}
	}

	$msg .= "If you need help configuring the DNS please contact your domain administrator for support.
	
Best regards,

Merijn Schering
Intermesh
	
	";


	echo $record['mailDomain']."\n\n";

//	echo $msg;

	go()->getMailer()
		->compose()
		->setSubject("Optimise your email deliverability")
		->setFrom("mschering@intermesh.nl", "Merijn Schering (Intermesh)")
		->setBody($msg, "text/plain")
		->setTo("info@" . $record['mailDomain'])
		->send();


	echo "\n\n\n\n\n=========\n\n\n\n\n";
}