<?php
\go\core\App::get()->setCache(new \go\core\cache\None());
require('../views/Extjs3/themes/Paper/pageHeader.php');

if(is_dir("/etc/groupoffice/" . $_SERVER['HTTP_HOST'])) {
    echo "<section><fieldset>";
    echo("A config folder was found in /etc/groupoffice/" . $_SERVER['HTTP_HOST'] .". Please move all your domain configuration folders from /etc/groupoffice/* into /etc/groupoffice/multi_instance/*. Only move folders, leave /etc/groupoffice/config.php and other files where they are.");
    echo "</fieldset></section>";

    require('footer.php');
    exit();
}
