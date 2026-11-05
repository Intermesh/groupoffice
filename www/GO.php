<?php
require_once(__DIR__ .'/vendor/autoload.php');

//Initialize new framework
use go\core\App;
use go\core\jmap\State;

App::get()->setAuthState(new State());

//initialize old framework
require_once(__DIR__ .'/go/GO.php');
GO::init();
