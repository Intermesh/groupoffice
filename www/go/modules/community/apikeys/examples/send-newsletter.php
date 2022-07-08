<?php
//This example sends a newsletter via the Group-Office API

//Adjust these variables for your installation
$apiKey = "61bb0ed6515d2da17c95e073a0458bdb4421273784cc3";
$apiUrl = 'http://localhost/api/jmap.php';

// make sure this list exists in Group-Office
$addressListId = 1;

// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);

// Create newsletter. Make sure the ID's exist. For the template syntax see:
// https://groupoffice.readthedocs.io/en/latest/using/newsletters.html#templates

$newsLetter = [
	"addressListId" => 1,
	"smtpAccountId" => 1,
	"subject" => "Hi {{contact.firstName}}",
	"body" => "<div><div>{{contact.salutation}},<div><div><br></div><div>Just testing!<br></div></div></div><div><br></div><div>Best regards,</div><div><br></div><div>{{creator.displayName}}</div></div><div>{{creator.profile.organizations[0].name}}</div><div><br></div><div><a href=\"{{unsubscribeUrl}}\">unsubscribe</a></div>",
	"attachments" => []
];


$data = [
	[
		"Newsletter/set",
		[
			"create" => [
				"new" => $newsLetter
			]
		],
		"clientCallId-1"
	]
];

$dataStr = json_encode($data);

// Make POST request with curl
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		"Authorization: Bearer " . $apiKey,
		'Content-Length: ' . strlen($dataStr))
);

$result = curl_exec($ch);

//check for request error.
if (!$result) {
	die("Failed to send request!" . curl_error($ch));
}

$responses = json_decode($result, true);

echo "<pre>";
var_dump($responses);
echo "</pre>";
