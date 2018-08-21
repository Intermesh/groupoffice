<?php
require(__DIR__ . "/vendor/autoload.php");

use go\core\App;
use go\core\fs\Blob;

App::get();

$blob = Blob::findById($_GET['blob']);
if (!$blob) {
	echo "Not found";
	http_response_code(404);
	exit();
}

$blob->getFile()->output(true, true, ['Content-Type' => $blob->type]);