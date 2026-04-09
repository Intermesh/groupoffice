<?php
require_once(dirname(__DIR__, 4) . '/vendor/autoload.php');
use go\core\App;
use go\core\http\Response;
use go\core\model\Module;

App::get();

$baseUrl = dirname(\go\core\http\Request::get()->getPath(), 5) . '/';

$importMap = [
	"@intermesh/goui" => $baseUrl . "node_modules/@intermesh/goui/dist/index.js?v=TODO",
	"@intermesh/groupoffice-core" => $baseUrl . "node_modules/@intermesh/groupoffice-core/dist/index.js?v=TODO"
];

$mods = Module::find();

foreach($mods as $mod) {
	$gouiScript = $mod->module()->getFolder()->getFile("views/goui/dist/Index.js");

	if($gouiScript->exists()) {
		$importMap["@intermesh/" . $mod->package ."-" . $mod->name] = $baseUrl . $gouiScript->getRelativePath(go()->getEnvironment()->getInstallFolder());
	}
}

?>
{
	"imports": <?= json_encode($importMap); ?>
}

