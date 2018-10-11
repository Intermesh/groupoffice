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

$input = fopen('php://input', "r");
$tmpName = GO()->getDataFolder()->getPath() . '/tmp/' . Request::get()->getHeader('X-File-Name') .'-'. microtime() . '.tmp';
if (!file_exists(dirname($tmpName))) {
	mkdir(dirname($tmpName), 0775);
}
$fp = fopen($tmpName, "w+");

while ($data = fread($input, 4096)) { // 4kb at the time
	fwrite($fp, $data);
}
fclose($fp);
fclose($input);

$blob = Blob::fromTmp($tmpName);
$blob->name = Request::get()->getHeader('X-File-Name');
$blob->modified = Request::get()->getHeader('X-File-LastModifed');
$blob->type = Request::get()->getContentType();
if ($blob->save()) {
	Response::get()->setStatus(201, 'Created');
	Response::get()->output([
		'blobId' => $blob->id,
		'type' => $blob->type,
		'size' => $blob->size
	]);
} else {
	echo 'Could not save '.$blob->id;
	print_r($blob->getValidationErrors());
}
