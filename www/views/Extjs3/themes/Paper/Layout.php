<?php

use go\core\App;
use go\core\webclient\Extjs3;

if(!isset($primaryColor)) {
	$primaryColor = (go()->getSettings()->primaryColor ?? 'rgb(2, 119, 189)');
}
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
	<meta name="apple-mobile-web-app-capable" content="no">
	<meta name="apple-mobile-web-app-title" content="<?= go()->getSettings()->title; ?>">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= GO::view()->getTheme()->getUrl(); ?>img/favicon/apple-touch-icon.png">
    <meta name="theme-color" content="<?= $primaryColor; ?>" />
    <meta name="msapplication-TileColor" content="<?= $primaryColor; ?>">
    <?php

	require(GO::config()->root_path.'views/Extjs3/default_scripts.inc.php');

	go()->fireEvent(App::EVENT_SCRIPTS);
	?>

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

	<title><?= go()->getSettings()->title; ?></title>
	<?php
	$cssMtime = filemtime(__DIR__ . "/style.css");
	?>
	<link rel="stylesheet" type="text/css" as="style"  href="<?= GO::view()->getTheme()->getUrl();?>style.css?v=<?=$cssMtime ?>" media="print, (min-device-width:1201px)" />
	<link rel="preload" type="text/css"as="style" media="screen and (max-device-width:1200px)" href="<?= GO::view()->getTheme()->getUrl(); ?>style-mobile.css?v=<?=$cssMtime;?>" />
	<link rel="stylesheet" type="text/css" as="style"  href="<?= GO::view()->getUrl()?>css.php?theme=<?=\GO::view()->getTheme()->getName(); ?>&v=<?=$webclient->getCSSFile(\GO::view()->getTheme()->getName())->getModifiedAt()->format("U"); ?>"  />
	<link rel="stylesheet" type="text/css" as="style"  href="<?= GO::view()->getUrl()?>css.php?theme=<?=\GO::view()->getTheme()->getName(); ?>&v=<?=$webclient->getCSSFile(\GO::view()->getTheme()->getName())->getModifiedAt()->format("U"); ?>"  />

	<?php

	//$this is \GO\Core\Controller\Auth
	GO::router()->getController()->fireEvent('head');
	go()->fireEvent(App::EVENT_HEAD);
	?>

   <style>
        <?php

        if(go()->getSettings()->logoId) {
                //blob id is not used by script but added only for caching.
                echo ".go-app-logo, #go-logo {background-image: url(" . go()->getSettings()->URL . "api/page.php/core/logo) !important}";
        }

        if(GO::view()->getTheme()->getName() == 'Paper' || isset($useThemeSettings)) {
            if(go()->getSettings()->primaryColor) {
                ?>
            :root {
                --c-primary: <?= '#'.go()->getSettings()->primaryColor; ?> !important;
                --c-header-bg: <?= '#'.go()->getSettings()->primaryColor; ?> !important;
                --c-primary-tp: <?= go()->getSettings()->getPrimaryColorTransparent(); ?> !important;
            }
            <?php
            }

            if(go()->getSettings()->secondaryColor) {
                ?>
                :root {
                    --c-secondary: <?= '#'.go()->getSettings()->secondaryColor; ?> !important;
                }
            <?php
            }

            if(go()->getSettings()->accentColor) {
                ?>
                :root {
                    --c-accent: <?= '#'.go()->getSettings()->accentColor; ?> !important;
                }
            <?php
            }
        }
		?>
	</style>

    <?php

    if(!empty(GO::config()->custom_css_url)){
	  echo '<link href="'. GO::config()->custom_css_url.'" type="text/css" rel="stylesheet" />';
    }
  ?>
</head>
<body class="go-compact">
	<div id="sound"></div>
    <div id="paper"></div>
	<!--Putting scripts in div will speed up developer tools because they don't have to show all those nodes-->
<!--	<div id="scripts-container">-->
<!--		-->
<!--	</div>-->
    <script type="text/javascript">
		 GO.util.density = GO.util.isMobileOrTablet() ? 160 :  <?= isset($density) ? $density : 140?>;
    </script>
</body>
</html>
