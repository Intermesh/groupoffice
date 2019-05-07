<?php
use go\core\fs\Blob;
use go\core\App;

require("../vendor/autoload.php");

$blob = Blob::findById(App::get()->getSettings()->logoId);

if (!$blob) {
  echo "Not found";
  http_response_code(404);
  exit();
}

$blob->output();	
