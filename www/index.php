<?php
require_once('vendor/autoload.php');
use go\core\App;
use go\core\model\Module;

App::get();
$baseUrl = \go\core\http\Request::get()->getPath() . "/";

?>

<!DOCTYPE html>
<html lang="en" style="height:100%">
<head>
    <title>Group-Office</title>
    <script>
			var BaseHref = "<?= $baseUrl ?>";
    </script>

    <script type="importmap">
		<?php

        $gouiScript = "node_modules/@intermesh/goui/dist/index.js";
        $coreScript = "node_modules/@intermesh/groupoffice-core/dist/index.js";
        $importMap = [
                "@intermesh/goui" => $baseUrl.$gouiScript ."?v=" . filemtime(__DIR__ . '/' . $gouiScript),
                "@intermesh/groupoffice-core" => $baseUrl.$coreScript . "?v=" . filemtime(__DIR__ . '/' . $coreScript)
        ];

        $mods = Module::find();

        foreach($mods as $mod) {
            $gouiScript = $mod->module()->getFolder()->getFile("views/goui/dist/Index.js");

            if($gouiScript->exists()) {
                $importMap["@intermesh/" . ($mod->package ?? "legacy") . "-" . $mod->name] = $baseUrl . $gouiScript->getRelativePath(go()->getEnvironment()->getInstallFolder()) ."?v=" . $gouiScript->getModifiedAt()->format("U");
            }
        }

        ?>
        {
            "imports": <?= json_encode($importMap); ?>
        }
    </script>

    <script src="views/Extjs3/javascript/ext-base-debug.js"></script>
    <script src="views/Extjs3/javascript/ext-all-debug.js"></script>
    <script src="views/Extjs3/lang.php"></script>
    <script src="views/goui/legacyscripts.php"></script>

    <script type="module" src="views/goui/dist/Index.js"></script>

    <meta name="theme-color" content="#000">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon"
          type="image/x-icon"
          href="./favicon.ico">

    <link rel="stylesheet" media="print,screen" href="views/Extjs3/themes/Paper/style.css">
    <link rel="stylesheet" href="node_modules/@intermesh/groupoffice-core/dist/groupoffice.css">
    <link rel="stylesheet" href="views/Extjs3/css.php">


<!--    <meta http-equiv="Content-Security-Policy"-->
<!--          content="default-src 'self' data:; style-src 'unsafe-inline' 'self'; script-src 'self'; child-src 'none'; connect-src 'self'; img-src data: blob: 'self'">-->

<body id="goui" style="height:100%"></body>