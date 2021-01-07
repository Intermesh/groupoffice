<?php
use go\core\App;
use go\core\jmap\State;
use go\core\fs\Blob;
use go\core\http\Client;
use go\core\http\Response;
use go\core\http\Request;

require("../vendor/autoload.php");

try {
//Create the app with the database connection
	App::get()->setAuthState(new State());
	if (!App::get()->getAuthState()->isAuthenticated()) {
		Response::get()->setStatus(401, 'Created');
		throw new \go\core\http\Exception(401);
	}

	if (isset($_GET['url'])) {
		$tmpFile = \go\core\fs\File::tempFile('tmp');
		$httpClient = new Client();
		$response = $httpClient->download($_GET['url'], $tmpFile);

		$blob = Blob::fromTmp($tmpFile);
		$blob->name = $response['name'];
		$blob->type = $response['type'];

	} else {

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
		$blob->modifiedAt = new \go\core\util\DateTime('@' . Request::get()->getHeader('X-File-LastModified'));
		//$blob->type = Request::get()->getContentType(); cant be trusted use extension instead
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
	Response::get()->setStatus(500, "Upload failed");
	Response::get()->setContentType("application/problem+json");
	Response::get()->output([
		"title" => "Upload failed",
		"detail" => $e->getMessage(),
		"status" => 500
	]);
}