<?php

use GO\Base\Util\Http;
use GO\Base\Util\StringHelper;
use go\core\App;

if(Http::isAjaxRequest()){
	if(App::get()->getDebugger()->enabled) {
		$data['response']['debug'] = App::get()->getDebugger()->getEntries();
	}
	echo $data['response'];
	
}elseif(PHP_SAPI=='cli'){
	echo "ERROR: ".trim($data['response']['feedback'])."\n\n";
	if(GO::config()->debug)
		echo $data['response']['exception']."\n\n";
}else
{
	require("externalHeader.php");
	echo '<h1>'.GO::t("Error").'</h1>';
	echo '<p style="color:red">'.  StringHelper::encodeHtml($data['response']['feedback']).'</p>';
	if(App::get()->getDebugger()->enabled){
		unset($data['response']['feedback']);
		echo '<h2>Debug info:</h2>';
		echo '<pre>';
		echo StringHelper::encodeHtml(var_export($data['response'], true));

		echo "\n\n-----\n\n";

		go()->getDebugger()->printEntries();
		echo '</pre>';

		
	}
	
	require("externalFooter.php");
}
