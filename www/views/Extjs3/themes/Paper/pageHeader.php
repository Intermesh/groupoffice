<?php

use go\core\webclient\Extjs3;

$webclient = Extjs3::get();
$lang = go()->getLanguage()->getIsoCode();
$themeUrl = $webclient->getThemeUrl();
$viewUrl = $webclient->getRelativeUrl() . 'views/Extjs3';

$goTitle = basename(dirname($_SERVER['PHP_SELF'])) == 'install' ? go()->t("Installation") : go()->getSettings()->title;
?>
<!DOCTYPE html>
<html lang="<?= $lang; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true"/>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?= $goTitle; ?>">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<link rel="apple-touch-icon" sizes="180x180" href="<?= $themeUrl; ?>img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $themeUrl; ?>img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $themeUrl; ?>img/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?= $themeUrl; ?>img/favicon/site.webmanifest">
    <link rel="mask-icon" href="<?= $themeUrl; ?>img/favicon/safari-pinned-tab.svg" color="#888888">

	<meta name="msapplication-TileColor" content="#2b5797">
	<meta name="theme-color" content="#ffffff">

	<title><?= $goTitle; ?><?= isset($title) ? '- ' . $title : "" ?></title>
	<?php
	$cssMtime = filemtime(__DIR__ . "/style.css");
	?>
	<link href="<?= $themeUrl;?>style.css?v=<?=$cssMtime ?>" media="print, (min-device-width:1201px)" type="text/css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" media="screen and (max-device-width:1200px)" href="<?= $themeUrl; ?>style-mobile.css?v=<?=$cssMtime;?>" />
    <link href="<?= $viewUrl; ?>/css.php?theme=<?=\GO::view()->getTheme()->getName(); ?>&v=<?=$webclient->getCSSFile(\GO::view()->getTheme()->getName())->getModifiedAt()->format("U"); ?>" type="text/css" rel="stylesheet" />

    <?php
    if(!empty(go()->getConfig()['custom_css_url'])) {
        echo '<link href="'. GO::config()->custom_css_url.'" type="text/css" rel="stylesheet" />';
    }

    if(isset($head)) {
        foreach($head as $h) {
            echo $h ."\n";
        }
    }
    ?>

	<style>
        <?php

	    if($webclient->getTheme() == 'Paper') {
				if(go()->getSettings()->primaryColor) {
						?>
		:root {
			--c-primary: <?= '#'.go()->getSettings()->primaryColor; ?> !important;
			--c-header-bg: <?= '#'.go()->getSettings()->primaryColor; ?> !important;
			--c-primary-tp: <?= go()->getSettings()->getPrimaryColorTransparent(); ?> !important;
		}

		<?php

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
if(go()->getSettings()->logoId) {
		//blob id is not used by script but added only for caching.
		echo ".go-app-logo, #go-logo {background-image: url(" . go()->getSettings()->URL . "api/page.php/core/logo) !important}";
}
}
?>
	</style>
</head>
<body class="go-page">

<header>
    <div class="go-app-logo"></div>
</header>

<section class="go-html-formatted">

