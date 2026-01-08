<?php
require_once(dirname(__DIR__, 4) . '/vendor/autoload.php');
use go\core\App;
use go\core\http\Response;
use go\core\model\Module;

App::get();

$mods = Module::find();

$baseUrl = dirname(\go\core\http\Request::get()->getPath(), 5) . '/';

$response = ['modules' => []];
foreach($mods as $mod) {
	$gouiScript = $mod->module()->getFolder()->getFile("views/goui/dist/Index.js");

	$r = [
		'name' => $mod->name,
		'package' => $mod->package,
//		'folder' => (string)$gouiScript
	];

	if($gouiScript->exists()) {
		$r['entry'] = $baseUrl . $gouiScript->getRelativePath(go()->getEnvironment()->getInstallFolder()) . '?v='. go()->getVersion();
	}
	$response['modules'][] = $r;
}

$response['languages'] = go()->getLanguage()->getLanguages();

Response::get()->output($response);