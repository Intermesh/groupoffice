<?php
//full url to Group-Office
$url = 'https://localhost/groupoffice/';

//put the right
$params=array(
	'task'=>'login',
	'username'=>'admin',
	'password'=>'admin'
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,$url.'action.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

//for self-signed certificates
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


$response = curl_exec($ch);
$lines = explode("\n", $response);

//last line is json response
$json = array_pop($lines);
$data = json_decode($json, true);

if(!$data['success']){
	die('Invalid login supplied');
}else
{
	//the rest are http headers. we need the session cookie
	foreach($lines as $header){
		if(preg_match('/Set-Cookie: groupoffice=(.*);/',$header, $matches)){

			$login_url = $url.'?session_id='.$matches[1].'&auth_token='.$data['auth_token'];

			header('Location: '.$login_url);
			exit();
			break;
		}
	}
}
die('Session cookie not found.');