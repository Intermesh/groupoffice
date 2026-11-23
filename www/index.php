<?php

// Block Apple's exchange accounts that will create a massive amount of requests on the server if we don't stop it here
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (str_starts_with($ua, 'AppleExchangeWebServices')) {
    http_response_code(404);
    exit;
}

require_once('vendor/autoload.php');
use go\core\App;
use go\core\jmap\State;
use go\core\webclient\Startup;

try {
	App::get()->setAuthState(new State());
	// Shows a page when the system is unavailable, and logs in with a token of the server manager or the remember me cookie
	Startup::run();
} catch(Throwable $e) {
	Startup::handleException($e);
	exit();
}

// Without the path and query of the request, eg. "?oauthConsent=1" or "index.php?lang=nl"
$baseUrl = \go\core\webclient\Extjs3::get()->getBaseUrl();

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

<?php
    $title = htmlspecialchars(go()->getSettings()->title);
    $primaryColor = htmlspecialchars(!empty(go()->getSettings()->primaryColor) ? '#' . go()->getSettings()->primaryColor : 'rgb(22, 82, 161)');
    $favicon = go()->getConfig()['favicon'] ?? null;
    $faviconUrl = "views/Extjs3/themes/Paper/img/favicon/";
    ?>
    <meta name="theme-color" content="<?= $primaryColor ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="HandheldFriendly" content="true">
    <meta name="apple-mobile-web-app-capable" content="no">
    <meta name="apple-mobile-web-app-title" content="<?= $title ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="msapplication-TileColor" content="<?= $primaryColor ?>">

<?php if(!empty($favicon)): ?>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($favicon) ?>">
<?php else: ?>
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $faviconUrl ?>favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $faviconUrl ?>favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $faviconUrl ?>apple-touch-icon.png">
    <link rel="manifest" href="views/site.webmanifest">
    <link rel="mask-icon" href="<?= $faviconUrl ?>safari-pinned-tab.svg" color="#888888">
<?php endif; ?>

    <link rel="stylesheet" media="print,screen" href="views/Extjs3/themes/Paper/style.css">
    <link rel="stylesheet" href="node_modules/@intermesh/groupoffice-core/dist/groupoffice.css">
    <link rel="stylesheet" href="views/Extjs3/css.php">
<?php
    // Custom logos of the system settings. The blob ID is added for caching only.
    $logoId = go()->getSettings()->logoId;
    $logoIdDark = go()->getSettings()->logoIdDark;
    if(!empty($logoId) || !empty($logoIdDark)): ?>
    <style>
<?php if(!empty($logoId)): ?>
        .groupoffice-logo, .login-overlay:after {
            background-image: url("api/page.php/core/logo?v=<?= urlencode($logoId) ?>");
        }
<?php endif; ?>
<?php if(!empty($logoIdDark)): ?>
        .dark .groupoffice-logo, .dark .login-overlay:after {
            background-image: url("api/page.php/core/logo/dark?v=<?= urlencode($logoIdDark) ?>");
        }
<?php endif; ?>
    </style>
<?php endif; ?>


<!--    <meta http-equiv="Content-Security-Policy"-->
<!--          content="default-src 'self' data:; style-src 'unsafe-inline' 'self'; script-src 'self'; child-src 'none'; connect-src 'self'; img-src data: blob: 'self'">-->

<body id="goui" style="height:100%"></body>