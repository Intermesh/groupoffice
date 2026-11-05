<?php
require_once('vendor/autoload.php');
use go\core\App;

App::get();
$baseUrl = \go\core\http\Request::get()->getFullUrl()  ;

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

        // Relative paths are cached, because the base URL depends on the request. Not cached when debugging, so
        // rebuilt scripts get a new version straight away. Upgrades and installs flush the cache.
        $importMap = go()->getDebugger()->enabled ? null : go()->getCache()->get("gouiImportMap");

        if(!$importMap) {
            $gouiScript = "node_modules/@intermesh/goui/dist/index.js";
            $coreScript = "node_modules/@intermesh/groupoffice-core/dist/index.js";
            $importMap = [
                    "@intermesh/goui" => $gouiScript ."?v=" . filemtime(__DIR__ . '/' . $gouiScript),
                    "@intermesh/groupoffice-core" => $coreScript . "?v=" . filemtime(__DIR__ . '/' . $coreScript)
            ];

            // All modules on disk, not only installed ones, because code may import from a module that is not installed.
            // Legacy modules in "modules/" use the "legacy" package in their specifier, eg. "@intermesh/legacy-email".
            $scripts = array_merge(
                    glob(__DIR__ . "/go/modules/*/*/views/goui/dist/Index.js") ?: [],
                    glob(__DIR__ . "/modules/*/views/goui/dist/Index.js") ?: []
            );

            foreach($scripts as $script) {
                $relativePath = substr($script, strlen(__DIR__) + 1);
                $parts = explode("/", $relativePath);
                $specifier = $parts[0] == "go" ? $parts[2] . "-" . $parts[3] : "legacy-" . $parts[1];

                $importMap["@intermesh/" . $specifier] = $relativePath . "?v=" . filemtime($script);
            }

            if(!go()->getDebugger()->enabled) {
                go()->getCache()->set("gouiImportMap", $importMap);
            }
        }

        $importMap = array_map(fn($url) => $baseUrl . $url, $importMap);

        ?>
        {
            "imports": <?= json_encode($importMap); ?>
        }
    </script>

    <script src="views/Extjs3/javascript/ext-base-debug.js"></script>
    <script src="views/Extjs3/javascript/ext-all-debug.js"></script>
    <script src="views/Extjs3/lang.php?lang=<?= $_GET['lang'] ?? ''?>"></script>
<!--    <script src="views/goui/legacyscripts.php"></script>-->

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