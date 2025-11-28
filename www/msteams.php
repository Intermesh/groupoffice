<?php

// ------------------------------------------------------
// 1. Azure AD application config
// ------------------------------------------------------

$redirectUri = 'http://localhost/msteams.php';

// Step 1: Redirect to login to get authorization code
if (!isset($_GET['code'])) {
	$authUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize?" . http_build_query([
			'client_id' => $clientId,
			'response_type' => 'code',
			'redirect_uri' => $redirectUri,
			'response_mode' => 'query',
			'scope' => 'User.Read OnlineMeetings.ReadWrite',
		]);
	header("Location: $authUrl");
	exit;
}

// Step 2: Exchange authorization code for access token
$code = $_GET['code'];
$tokenUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";

$postFields = http_build_query([
	'client_id' => $clientId,
	'scope' => 'User.Read OnlineMeetings.ReadWrite',
	'code' => $code,
	'redirect_uri' => $redirectUri,
	'grant_type' => 'authorization_code',
	'client_secret' => $clientSecret,
]);

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'];

// Step 3: Create Teams meeting
$meetingUrl = "https://graph.microsoft.com/v1.0/me/onlineMeetings";

$meetingData = [
	"startDateTime" => "2025-11-28T10:00:00Z",
	"endDateTime" => "2025-11-28T11:00:00Z",
	"subject" => "Test Meeting via Delegated Flow"
];

$ch = curl_init($meetingUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($meetingData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
	"Authorization: Bearer $accessToken",
	"Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;