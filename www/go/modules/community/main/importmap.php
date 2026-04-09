<?php
require_once(dirname(__DIR__, 4) . '/vendor/autoload.php');
use go\core\App;
use go\core\http\Response;
use go\core\model\Module;

App::get();

$baseUrl = dirname(\go\core\http\Request::get()->getPath(), 5) . '/';

$gouiScript = $baseUrl . "node_modules/@intermesh/goui/dist/index.js";
$coreScript = $baseUrl . "node_modules/@intermesh/groupoffice-core/dist/index.js";
$importMap = [
	"@intermesh/goui" => $gouiScript ."?v=" . filemtime("../../../.." . $gouiScript),
	"@intermesh/groupoffice-core" => $coreScript . "?v=" . filemtime("../../../.." . $coreScript)
];

$mods = Module::find();

foreach($mods as $mod) {
	$gouiScript = $mod->module()->getFolder()->getFile("views/goui/dist/Index.js");

	if($gouiScript->exists()) {
		$importMap["@intermesh/" . $mod->package ."-" . $mod->name] = $baseUrl . $gouiScript->getRelativePath(go()->getEnvironment()->getInstallFolder()) ."?v=" . $gouiScript->getModifiedAt()->format("U");
	}
}

?>
{
	"imports": <?= json_encode($importMap); ?>
}

