<?php

use go\core\App;

require('../vendor/autoload.php');
App::get();

// Called by the CLI to clear the APCu cache of the web server. Only flush the cache here. The CLI
// rebuilds the cache itself, often inside a transaction. Rebuilding it here too would write the same
// rows from another connection and break that transaction.
go()->getCache()->flush(false);