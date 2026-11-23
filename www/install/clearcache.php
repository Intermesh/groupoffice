<?php

use go\core\App;
use go\core\http\Response;
use go\core\util\Crypt;

require('../vendor/autoload.php');
App::get();

// Called by the CLI after an upgrade. Requires a token signed with the server key so it can't be triggered anonymously.
if (!Crypt::verifyInternalToken($_SERVER['HTTP_X_GO_CACHE_TOKEN'] ?? '', 'clearcache')) {
	Response::get()->setStatus(403);
	exit();
}

go()->rebuildCache();
