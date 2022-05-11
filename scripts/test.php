<?php

use go\core\http\Client;

chdir(__DIR__);
require ('../www/GO.php');


$data = [
	[
		"Foo/set",[
			"create" => [
				"clientId-1" => [
					"hostname" => 'test.groupoffice.net',
					"isTrial" => true,
					"storageQuota" => 1024 * 1024 * 1024,
				]
			]
	],"clientCallId-1"]
];

$client = new Client();
//$client->setHeader('Authorization',"Bearer 5af938db7eeb5653b945f8df4936ac65f8ff3da47a804");
//$response = $client->get("https://manage.groupoffice.net/api/jmap.php");

$client->setHeader('Authorization',"Bearer 627a54228608dedda4f244753a28147bce377929a21c6");
$response = $client->get("http://localhost/api/jmap.php?XDEBUG_SESSION=1");

var_dump($response);