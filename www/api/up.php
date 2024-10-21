<?php
/**
 * Checks if system is OK. Can be used for uptime monitoring
 */
use go\core\App;
use go\core\ErrorHandler;
use go\core\http\Response;

require("../vendor/autoload.php");

$minSpace = 1024 * 1024 * 1024; // start erroring on 1G disk space left

try {
	// check read db
	$settings = App::get()->getDbConnection()->select()->from("core_setting")->limit(1)->single();

	if(disk_free_space(go()->getDataFolder()->getPath()) < $minSpace) {
		Response::get()->setStatus(500);
		ErrorHandler::log("Out of disk space");
		exit();
	}

	if(!go()->getTmpFolder()->getFile("log/up.txt")->putContents(date("c"))) {
		Response::get()->setStatus(500);
		ErrorHandler::log("Can't write to data");
		exit();
	}


	if(disk_free_space(go()->getTmpFolder()->getPath()) < $minSpace) {
		Response::get()->setStatus(500);
		ErrorHandler::log("Out of disk space");
		exit();
	}

	if(!go()->getTmpFolder()->getFile("up.txt")->putContents(date("c"))) {
		Response::get()->setStatus(500);
		ErrorHandler::log("Can't write to tmp");
		exit();
	}

	echo "All OK";


} catch(Exception $e) {
	Response::get()->setStatus(500);
	echo "Internal Server Error";
	ErrorHandler::logException($e);
}