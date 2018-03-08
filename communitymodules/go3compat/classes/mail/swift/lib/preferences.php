<?php
require_once(dirname(__FILE__).'/../../../../Group-Office.php');

// Sets the default charset so that setCharset() is not needed elsewhere
Swift_Preferences::getInstance()->setCharset('utf-8');

Swift_Preferences::getInstance()
    -> setTempDir($GLOBALS['GO_CONFIG']->tmpdir)
    -> setCacheType('disk');