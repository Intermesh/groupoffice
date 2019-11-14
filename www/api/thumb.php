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
	http_response_code(403);
	exit("Unauthorized.");
}

if (!isset($_GET['blob'])) {
	http_response_code(400);
	exit("Bad request. Param 'blob' must be given.");
}

$blob = Blob::findById($_GET['blob']);
if (!$blob) {
	echo "Not found";
	http_response_code(404);
	exit();
}

if ($blob->size > 10 * 1024 * 1024) {
	throw new \Exception("Image may not be larger than 10MB");
}


$w = isset($_GET['w']) ? intval($_GET['w']) : 0;
$h = isset($_GET['h']) ? intval($_GET['h']) : 0;
$zc = !empty($_GET['zc']) && !empty($w) && !empty($h);

$lw = isset($_GET['lw']) ? intval($_GET['lw']) : 0;
$lh = isset($_GET['lh']) ? intval($_GET['lh']) : 0;

$pw = isset($_GET['pw']) ? intval($_GET['pw']) : 0;
$ph = isset($_GET['ph']) ? intval($_GET['ph']) : 0;


$cacheFilename = $blob->id . '_' . $w . '_' . $h . '_' . $lw . '_' . $ph . '_' . $pw . '_' . $lw;
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
	throw new \Exception("Could not load image");
} else {
	if ($zc) {
		$image->zoomcrop($w, $h);
	} else {
		if ($lw || $lh || $pw || $lw) {
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
