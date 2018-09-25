<?php
use go\core\App;
use go\core\jmap\State;
use go\core\fs\Blob;
use go\core\http\Response;
use go\core\http\Request;

require(__DIR__ . "/vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());
if (!App::get()->getAuthState()->isAuthenticated()) {
	throw new \go\core\http\Exception(401);
}

Response::get()->setHeader('Cache-Control', 'no-cache');

// When the client is sending a BlobId header te server can check if the file already exists before uploading
$blobId = Request::get()->getHeader('X-BlobId');
if($blobId) {
	$blob = Blob::findById($blobId);
	if($blob) {
		Response::get()->setStatus(201, 'Created');
		Response::get()->output([
			'blobId' => $blob->id,
			'contentType' => $blob->contentType,
			'size' => $blob->size,
			'alreadyExists' => true
		]);
	} else {
		Response::get()->setStatus(204, 'No Content');
	}
	exit();
}

$input = fopen('php://input', "r");

$tmpFile = \go\core\fs\File::tempFile('tmp');

$fp = $tmpFile->open("w+");

while ($data = fread($input, 4096)) { // 4kb at the time
	fwrite($fp, $data);
}
fclose($fp);
fclose($input);

$blob = Blob::fromTmp($tmpFile);
$blob->name = Request::get()->getHeader('X-File-Name');

// Local modified at?
$blob->modifiedAt = new \go\core\util\DateTime('@' . Request::get()->getHeader('X-File-LastModifed'));
$blob->type = Request::get()->getContentType();
if ($blob->save()) {
	Response::get()->setStatus(201, 'Created');
	Response::get()->output([
		'blobId' => $blob->id,
		'contentType' => $blob->contentType,
		'size' => $blob->size
	]);
} else {
	echo 'Could not save '.$blob->id;
	
	var_dump(GO()->getDebugger()->getEntries());
}
