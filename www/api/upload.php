<?php
use go\core\App;
use go\core\jmap\State;
use go\core\fs\Blob;
use go\core\http\Response;
use go\core\http\Request;

require("../vendor/autoload.php");

App::get();

Response::get()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
Response::get()->setHeader('Pragma', 'no-cache');
Response::get()->setHeader('Expires', '01-07-2003 12:00:00 GMT');

if(Request::get()->getMethod() == 'OPTIONS') {
	Response::get()->output();
	exit();
}

try {
//Create the app with the database connection
	App::get()->setAuthState(new State());
	if (!App::get()->getAuthState()->isAuthenticated()) {
		Response::get()->setStatus(401, 'Created');
		throw new \go\core\http\Exception(401);
	}

	//raise max_execution_time for calculating hash of large files
	ini_set('max_execution_time', 300);

	$filename = Request::get()->getHeader('X-File-Name');
	$filename = Request::headerDecode($filename);
	$tmpFile = \go\core\fs\File::tempFile($filename);

	$input = fopen('php://input', "r");
	$fp = $tmpFile->open("w+");
	while ($data = fread($input, 4096)) { // 4kb at the time
		fwrite($fp, $data);
	}
	fclose($fp);
	fclose($input);

	if (\go\core\jmap\Capabilities::get()->maxSizeUpload && $tmpFile->getSize() > \go\core\jmap\Capabilities::get()->maxSizeUpload) {
		$tmpFile->delete();
		Response::get()->setStatus(413);
		Response::get()->output([
			"error" => "File exceeds maximum size of " . \go\core\jmap\Capabilities::get()->maxSizeUpload . " bytes"
		]);
		exit();
	}

	$blob = Blob::fromTmp($tmpFile);
	$blob->name = $filename;
	if (Request::get()->getHeader('X-File-LastModified') == null) {
		$blob->modifiedAt = new \go\core\util\DateTime();
	} else {
		$blob->modifiedAt = new \go\core\util\DateTime('@' . Request::get()->getHeader('X-File-LastModified'));
	}

	if ($blob->save()) {
		Response::get()->setStatus(201, 'Created');
		$response = $blob->toArray();
		$response['blobId'] = $blob->id; //deprecated
		Response::get()->output($response);
	} else {

		throw new Exception("Could not save file: ". $blob->getValidationErrorsAsString());
	}
}
catch(\Exception $e) {

	\go\core\ErrorHandler::logException($e);

	Response::get()->setStatus(500, "Upload failed");
	Response::get()->setContentType("application/problem+json");

	$response = [
		"title" => "Upload failed",
		"detail" => get_class($e),
		"status" => 500
	];

	if(go()->getDebugger()->enabled) {
		$response['debug'] = go()->getDebugger()->getEntries();
	}

	Response::get()->output($response);
}