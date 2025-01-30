<?php
require (dirname(__DIR__) . '/www/GO.php');

use go\core\http\Client;

$client = new Client();
$client->setOption(CURLOPT_CONNECTTIMEOUT, 120);
$client->setHeader("Connection", "close");
$response = $client->postJson("http://libretranslate:5010/translate", [
	"q" => "Categories",
	"source" => "en",
	"target" => "nl",
	"format" => "text",
	"alternatives" => 0
]);


echo $response['body']['translatedText'] ."\n";