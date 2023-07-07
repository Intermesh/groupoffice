<?php

use go\core\App;
use go\core\cache\None;

App::get()->setCache(new None());
// no logged in user because it can cause trouble with tables not existing yet
App::get()->setAuthState(new \go\core\auth\TemporaryState(null));

require('../views/Extjs3/themes/Paper/pageHeader.php');

try {
	if(is_dir("/etc/groupoffice/" . $_SERVER['HTTP_HOST'])) {
	    echo "<section><fieldset>";
	    echo("A config folder was found in /etc/groupoffice/" . $_SERVER['HTTP_HOST'] .". Please move all your domain configuration folders from /etc/groupoffice/* into /etc/groupoffice/multi_instance/*. Only move folders, leave /etc/groupoffice/config.php and other files where they are.");
	    echo "</fieldset></section>";

	    require('footer.php');
	    exit();
	}
} catch (Throwable $e) {
	// ignore possible openbase_dir restriction from is_dir()
}
