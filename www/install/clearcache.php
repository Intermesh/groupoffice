<?php

use go\core\App;

require('../vendor/autoload.php');
App::get();

go()->rebuildCache();
