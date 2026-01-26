<?php

require("../../../../../vendor/autoload.php");

use go\core\App;
use go\core\jmap\State;

try {
	App::get()->setAuthState(new State());

	if (!App::get()->getAuthState()->isAuthenticated()) {
		throw new \go\core\exception\Unauthorized();
	}

	if (empty($_POST['fileId'])) {
		throw new \Exception("Missing required parameter: fileId");
	}

	if (empty($_FILES['pdf'])) {
		throw new \Exception("Missing required file upload: pdf");
	}

	$fileId = $_POST['fileId'];
	$pdf = $_FILES['pdf'];

	if (empty($pdf['tmp_name'])) {
		throw new \Exception("Server could not find file");
	}

	$existingFile = \GO\Files\Model\File::model()->findByPk($fileId);

	if (!$existingFile) {
		throw new \GO\Base\Exception\NotFound("File not found with ID: " . $fileId);
	}

	$uploadedFile = new \GO\Base\Fs\File($pdf['tmp_name']);

	if (!$existingFile->replace($uploadedFile, true)) {
		throw new \Exception("Could not replace file with uploaded content: " . $uploadedFile);
	}

	http_response_code(200);
	echo json_encode([
		'success' => true
	]);

} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => 'Exception: ' . $e->getMessage(),
		'trace' => $e->getTraceAsString()
	]);
}