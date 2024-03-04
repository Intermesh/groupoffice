<?php
// Script used to transfer all mailboxes from Office 365 to our Group-Office Mailserver.

// CSV with 4 columns:
// user1, pass1, user2, pass2
// Note that pass1 contains only "_" as we used a master user that has access to all mail accounts.
// We used that user to generate an oauth2 access token.

// Note: we also used this file to import users into Group-Office and automatically created mail accounts
// using the serverclient module.

$source = "users.csv";

$separator = ';';
$enclosure = '"';

//tokens generated using https://imapsync.lamiral.info/oauth2/oauth2_office365/README.txt
//$oauthAccessToken1 = "./oauth2_office365/tokens/oauth2_tokens_mailuser@uxample.nl.txt";

// Target IMAP host using regular auth
$targetHost = 'target.imap.local';

$sourceHost = 'source.imap.local';

$fp = fopen($source, "r");

$headers = fgetcsv($fp,null, $separator, $enclosure);

if(count($headers) < 4) {
	echo "Invalid CSV. Read record: ";

	var_dump($headers);

	exit(1);
}

while($record = fgetcsv($fp,null, $separator, $enclosure)) {

	$user1 = $record[0];
	$pass1 = $record[1];
	$user2 = $record[2];
	$pass2 = $record[3];

	$cmd = 'imapsync --syncinternaldates ';

	if(!empty($oauthAccessToken1)) {
		$cmd .= '--oauthaccesstoken1 '.escapeshellarg($oauthAccessToken1).' ';
		$cmd .= '--office1 ';
	}

	if(isset($sourceHost)) {
		$cmd .= ' --host1 '. escapeshellarg($sourceHost).' ';
	}

	$cmd .= '--user1 ' . escapeshellarg($user1) . ' --password1 '.escapeshellarg($pass1).' ' .
		'--host2 ' . escapeshellarg($targetHost ). ' --tls2 --user2 ' . escapeshellarg($user2) . ' --password2 ' . escapeshellarg($pass2). ' ' .
		'--subscribeall --allowsizemismatch --nofoldersizes ' .
		'--sep1 / --sep2 . --regextrans2 "s,/,_,g"';

	echo $cmd . "\n";
}
