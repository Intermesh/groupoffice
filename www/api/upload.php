<?php
use go\core\App;
use go\core\jmap\State;
use go\core\fs\Blob;
use go\core\http\Client;
use go\core\http\Response;
use go\core\http\Request;

require("../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());
if (!App::get()->getAuthState()->isAuthenticated()) {
	throw new \go\core\http\Exception(401);
}

$tmpFile = \go\core\fs\File::tempFile('tmp');

if(isset($_GET['url'])) {
	$httpClient = new Client();
	$response = $httpClient->download($_GET['url'], $tmpFile);

	$blob = Blob::fromTmp($tmpFile);
	$blob->name = $response['name'];
	$blob->type = $response['type'];

} else {
	$input = fopen('php://input', "r");
	$fp = $tmpFile->open("w+");
	while ($data = fread($input, 4096)) { // 4kb at the time
		fwrite($fp, $data);
	}
	fclose($fp);
	fclose($input);

	$blob = Blob::fromTmp($tmpFile);
	$blob->name = Request::get()->getHeader('X-File-Name');
	$blob->modifiedAt = new \go\core\util\DateTime('@' . Request::get()->getHeader('X-File-LastModifed'));
	$blob->type = Request::get()->getContentType();
}


if ($blob->save()) {
	Response::get()->setStatus(201, 'Created');
	Response::get()->output([
		'blobId' => $blob->id,			
		'name' => $blob->name,
		'type' => $blob->type,
		'size' => $blob->size
	]);
} else {
	echo 'Could not save '.$blob->id;
	
	var_dump(GO()->getDebugger()->getEntries());
}
