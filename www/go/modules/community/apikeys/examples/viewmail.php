<?php
//This example shows how to view an e-mail and get some custom headers. You need to know the uid, ]
//mailbox and account_id in Group-Office to make it work.


//Adjust these variables for your installation:
$apiKey = 'your-api-key';
$baseUrl = 'http://host.docker.internal/';
$params = [
  'uid' => 616,
  'mailbox' => 'INBOX',
  'account_id' => 3,
  'customHeaders' => 'X-MySpecial-id,X-MySpecial-Category'
];



// For debugging display all errors
ini_set('display_errors', 'on');
error_reporting(E_ALL);


$ch = curl_init($baseUrl . '/index.php?r=email/message/view'); // Old framework! Will change to JMAP someday
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $apiKey
    )
);

$result = curl_exec($ch);

$result = json_decode($result, true);

echo "<pre>";

var_dump($result);




