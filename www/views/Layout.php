<?php
use go\core\App;
use go\core\webclient\Extjs3;

// options
$webclient = Extjs3::get();
$webclient->loadExt ??= true;
$webclient->loadGoui ??= true;

$goTitle = basename(dirname($_SERVER['PHP_SELF'])) == 'install' ? go()->t("Installation") : go()->getSettings()->title;
$primaryColor = go()->getSettings()->primaryColor ?? 'rgb(22, 82, 161)';

$webclient->loadScripts();
$themeUrl = $webclient->getThemeUrl();
$authController = new \GO\Core\Controller\AuthController(); // for some reason the event listeners are in this class
$cssMtime = filemtime(GO::view()->getTheme()->getPath() . "style.css");
$lang = go()->getLanguage()->getIsoCode();
?><!DOCTYPE html>
<html lang="<?= $lang; ?>" dir="<?=go()->getLanguage()->getTextDirection();?>">
<head>
	<?php
    if($webclient->loadExt) {
        $authController->fireEvent('headstart');
    }?>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="no">
	<meta name="apple-mobile-web-app-title" content="<?= $goTitle; ?>">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="theme-color" content="<?= $primaryColor; ?>">
	<meta name="msapplication-TileColor" content="<?= $primaryColor; ?>">

<?php if(!empty(GO::config()->favicon)): ?>
    <link href="<?=GO::config()->favicon?>" rel="shortcut icon" type="image/x-icon">
<?php else: ?>
    <link rel="icon" type="image/png" sizes="32x32" href="<?=$themeUrl?>img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=$themeUrl?>img/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$themeUrl;?>img/favicon/apple-touch-icon.png">
    <link rel="manifest" href="<?=$themeUrl?>img/favicon/site.webmanifest">
    <link rel="mask-icon" href="<?=$themeUrl?>img/favicon/safari-pinned-tab.svg" color="#888888">
<?php endif; ?>
    <title><?= $goTitle; ?><?= isset($title) ? ' - ' . $title : "" ?></title>

    <link rel="preload" href="<?= $themeUrl;?>fonts/icons.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" media="print, screen"  href="<?= $themeUrl;?>style.css?v=<?=$cssMtime ?>">
    <link rel="stylesheet" href="<?= $webclient->getBaseUrl();?>views/goui/dist/groupoffice-core/style/<?= $webclient->gouiStyleSheet ?>">

<?php if($webclient->loadExt) {
	$webclient->drawScripts();
	go()->fireEvent(App::EVENT_SCRIPTS);
	$authController->fireEvent('head');
	go()->fireEvent(App::EVENT_HEAD);
	if (!\go\core\Installer::isInstalling()):?>
		<link rel="stylesheet" href="<?= GO::view()->getUrl()?>css.php?theme=<?=$themeUrl; ?>&v=<?=$webclient->getCSSFile(\GO::view()->getTheme()->getName())->getModifiedAt()->format("U"); ?>" />
	<?php endif;
}

if(!empty(GO()->getConfig()['custom_css_url'])): ?>
    <link rel="stylesheet" href="<?=GO::config()->custom_css_url?>">
<?php endif; ?>

<style>
<?php if(go()->getSettings()->logoId): //blob id is not used by script but added only for caching. ?>
    .go-app-logo, #go-logo {
        background-image: url(<?=go()->getSettings()->URL?>api/page.php/core/logo) !important
    }
<?php endif; ?>
<?php if(isset($webclient->useThemeSettings)): ?>
    :root, body{
        <?= go()->getSettings()->printCssVars(); ?>
    }
    body.dark{
        <?= go()->getSettings()->printCssVars('Dark'); ?>
    }
<?php endif; ?>
</style>
</head>
<body class="<?=$webclient->bodyCls;?>">
<div id="paper"><!-- dom for printing will be inserted into this DIV --></div>