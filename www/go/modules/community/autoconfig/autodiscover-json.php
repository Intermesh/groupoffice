<?php
use go\core\App;
require("../../../../vendor/autoload.php");
App::get();

$easUrl = \go\core\jmap\Request::get()->getBaseUrl() . "/Microsoft-Server-ActiveSync";
header('Content-type: application/json');
if (strtolower($_GET['Protocol']) == 'activesync') {
	echo '{"Protocol":"ActiveSync","Url":"' . $easUrl . '"}';
}
elseif (strtolower($_GET['Protocol']) == 'autodiscoverv1') {
	echo '{"Protocol":"AutodiscoverV1","Url":"' . \go\core\jmap\Request::get()->getBaseUrl() . '/Autodiscover/Autodiscover.xml"}';
}
else {
	http_response_code(400);
	echo '{"ErrorCode":"InvalidProtocol","ErrorMessage":"The given protocol value \u0027' . preg_replace("/[^\da-z]/i", '', $_GET['Protocol']) . '\u0027 is invalid. Supported values are \u0027ActiveSync,AutodiscoverV1\u0027"}';
}