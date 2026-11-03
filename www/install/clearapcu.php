<?php

use go\core\App;
use go\core\http\Response;
use go\core\util\Crypt;

require('../vendor/autoload.php');
App::get();

// Requires a token signed with the server key so it can't be triggered anonymously.
if (!Crypt::verifyInternalToken($_SERVER['HTTP_X_GO_CACHE_TOKEN'] ?? '', 'clearapcu')) {
	Response::get()->setStatus(403);
	exit();
}

// Called by the CLI to clear the APCu cache of the web server. Only flush the cache here. The CLI
// rebuilds the cache itself, often inside a transaction. Rebuilding it here too would write the same
// rows from another connection and break that transaction.
go()->getCache()->flush(false);