<?php
$root = dirname(__FILE__).'/';
require_once($root.'vendor/autoload.php');

//Initialize new framework
use go\core\App;
use go\core\jmap\State;

App::get()->setAuthState(new State());

//initialize old framework
require_once($root.'go/GO.php');
GO::init();
