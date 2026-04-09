<?php
require_once(dirname(__DIR__, 4) . '/vendor/autoload.php');
use go\core\App;
use go\core\http\Response;
use go\core\model\Module;

App::get();

$mods = Module::find();

$baseUrl = dirname(\go\core\http\Request::get()->getPath(), 5) . '/';

$response = [
	'title' => go()->getSettings()->title,
	'modules' => []
];

foreach($mods as $mod) {
	$gouiScript = $mod->module()->getFolder()->getFile("views/goui/dist/Index.js");

	$r = [
		'name' => $mod->name,
		'package' => $mod->package,
//		'folder' => (string)$gouiScript
	];

	if($gouiScript->exists()) {
		// @intermesh/addressbook-main is revolved via an importmap
		$r['entry'] =  "@intermesh/" . $mod->package . "-" . $mod->name; //$baseUrl . $gouiScript->getRelativePath(go()->getEnvironment()->getInstallFolder());
	}
	$response['modules'][] = $r;
}

$response['languages'] = go()->getLanguage()->getLanguages();

$response['settings'] = go()->getSettings()->toArray();

Response::get()->output($response);