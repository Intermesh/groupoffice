<?php

use go\core\App;
use go\core\webclient\CSP;
use go\core\webclient\Extjs3;

$webclient = Extjs3::get();
$lang = GO::language()->getLanguage(); ?>
<!DOCTYPE html>
<html lang="<?= $lang; ?>">
<head>
	<?php GO::router()->getController()->fireEvent('headstart'); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true"/>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?= GO::config()->title; ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= GO::view()->getTheme()->getUrl(); ?>img/favicon/apple-touch-icon.png">

    <?php
	if(!empty(GO::config()->favicon)) {
		echo '<link href="'. GO::config()->favicon.'" rel="shortcut icon" type="image/x-icon">';
	} else {
	?>

	<link rel="icon" type="image/png" sizes="32x32" href="<?= GO::view()->getTheme()->getUrl(); ?>img/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= GO::view()->getTheme()->getUrl(); ?>img/favicon/favicon-16x16.png">
	<link rel="manifest" href="<?= GO::view()->getTheme()->getUrl(); ?>img/favicon/site.webmanifest">
	<link rel="mask-icon" href="<?= GO::view()->getTheme()->getUrl(); ?>img/favicon/safari-pinned-tab.svg" color="#888888">
	<?php } ?>

	<meta name="msapplication-TileColor" content="#2b5797">
	<meta name="theme-color" content="#ffffff">

	<title><?= GO::config()->title; ?></title>
	<?php
	$cssMtime = filemtime(__DIR__ . "/style.css");
	?>
	<link href="<?= GO::view()->getTheme()->getUrl();?>style.css?v=<?=$cssMtime ?>" media="print, (min-device-width:1201px)" type="text/css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" media="screen and (max-device-width:1200px)" href="<?= GO::view()->getTheme()->getUrl(); ?>style-mobile.css?v=<?=$cssMtime;?>" />
	<link href="<?= GO::view()->getUrl()?>css.php?v=<?=$webclient->getCSSFile()->getModifiedAt()->format("U"); ?>" type="text/css" rel="stylesheet" />

	<?php
	if(!empty(GO::config()->custom_css_url))
		echo '<link href="'. GO::config()->custom_css_url.'" type="text/css" rel="stylesheet" />';

	//$this is \GO\Core\Controller\Auth
	GO::router()->getController()->fireEvent('head');
	go()->fireEvent(App::EVENT_HEAD);
	?>
	<style>
        <?php
        if(GO::view()->getTheme()->getName() == 'Paper') {
            if(go()->getSettings()->primaryColor) {
                ?>
            :root {
                --c-primary: <?= '#'.go()->getSettings()->primaryColor; ?> !important;
                --c-header-bg: <?= '#'.go()->getSettings()->primaryColor; ?> !important;
                --c-primary-tp: <?= go()->getSettings()->getPrimaryColorTransparent(); ?> !important;
            }

            <?php
            }
            if(go()->getSettings()->logoId) {
                //blob id is not used by script but added only for caching.
                echo ".go-app-logo, #go-logo {background-image: url(" . go()->getSettings()->URL . "api/page.php?blob=" . go()->getSettings()->logoId . ") !important}";
            }
        }
        ?>
	</style>
	<meta http-equiv="Content-Security-Policy" content="<?= CSP::get(); ?>">
</head>
<body>
	<div id="sound"></div>
	<!--Putting scripts in div will speed up developer tools because they don't have to show all those nodes-->
	<div id="scripts-container">
		<?php 
		
		require(GO::config()->root_path.'views/Extjs3/default_scripts.inc.php');
		
		go()->fireEvent(App::EVENT_SCRIPTS);
		?>
	</div>
</body>
</html>
