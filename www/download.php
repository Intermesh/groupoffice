<?php
require(__DIR__ . "/vendor/autoload.php");

use go\core\App;
use go\core\fs\Blob;

App::get();

$blob = Blob::findById($_GET['blob']);
if (empty($blob) || !file_exists($blob->path())) {
	echo $blob->path() . ' not found';
	http_response_code(404);
	exit();
}
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $blob->name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $blob->size);
readfile($blob->path());