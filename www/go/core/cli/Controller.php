<?php

namespace go\core\cli;

use go\core\Controller as CoreController;

/**
 * CLI Controller.
 * 
 * You can execute controller methods via the CLI router.
 * 
 * 
 * You can run a CLI controller method like this:
 * 
 * ```
 * php cli.php package/modulename/controller/method --arg1=foo
 * ```
 * 
 * Or with Docker Compose:
 * ```
 * docker-compose exec --user www-data groupoffice php cli.php community/addressbook/migrate/run
 * ```
 * 
 * @see Router
 */
abstract class Controller extends CoreController {
	
}
