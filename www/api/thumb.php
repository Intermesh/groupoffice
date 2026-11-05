<?php

/**
 * Download entry point
 * 
 * GET parameter "blob" is required. Typically a blob hash from the core_blob table.
 * 
 * You can also implement custom download methods in "Module.php" of your module.
 * Methods prefixed with "download" can be accessed. For example method 
 * go\modules\community\addressbook\Module::downloadVcard($contactId) 
 * can be accessed with: "download.php?blob=community/addressbook/vcard/1"
 * 
 */
require("../vendor/autoload.php");

use go\core\App;
use go\core\fs\Blob;
use go\core\jmap\State;
use go\core\util\Image;

if (!App::get()->setAuthState(new State())->getAuthState()->isAuthenticated()) {
	http_response_code(401);
	exit("Unauthorized.");
}

if (!isset($_GET['blob'])) {
	http_response_code(400);
	exit("Bad request. Param 'blob' must be given.");
}

$blob = Blob::findById($_GET['blob']);
if (!$blob) {
	http_response_code(404);
	exit("Not found");
}

if ($blob->size > 10 * 1024 * 1024) {
	http_response_code(413);
	exit("Image may not be larger than 10MB");
}

// Requested sizes are user input and determine memory usage: keep them within sane bounds
const MAX_DIMENSION = 4096;
$dimension = fn(string $name): int => isset($_GET[$name]) ? max(0, min(MAX_DIMENSION, intval($_GET[$name]))) : 0;

$w = $dimension('w');
$h = $dimension('h');
$zc = !empty($_GET['zc']) && !empty($w) && !empty($h);

$lw = $dimension('lw');
$lh = $dimension('lh');

$pw = $dimension('pw');
$ph = $dimension('ph');


$cacheFilename = $blob->id . '_' . $w . '_' . $h . '_' . $lw . '_' . $lh . '_' . $pw . '_' . $ph;
$cacheDir = go()->getTmpFolder()->getFolder('thumbcache')->create();

if ($zc) {
	$cacheFilename .= '_zc';
}

$cacheFile = $cacheDir->getFile($cacheFilename);

if ($cacheFile->exists()) {
	$cacheFile->output(true, true, [], true);
	exit();
} 

$image = new Image($blob->path());
if (!$image->loadSuccess) {
	http_response_code(415);
	exit("Could not load image");
} else {
	if($image->getImageType() == IMAGETYPE_JPEG) {
		$image->fixOrientation();
	}
	if ($zc) {
		$image->zoomcrop($w, $h);
	} else {
		if ($lw || $lh || $pw || $ph) {
			//treat landscape and portrait differently
			$landscape = $image->landscape();
			if ($landscape) {
				$w = $lw;
				$h = $lh;
			} else {
				$w = $pw;
				$h = $ph;
			}
		}

		if ($w && $h) {
			$image->resize($w, $h);
		} elseif ($w) {
			$image->resizeToWidth($w);
		} else {
			$image->resizeToHeight($h);
		}
	}
	$image->save($cacheFile->getPath());
}

$cacheFile->output(true, true, [], true);
