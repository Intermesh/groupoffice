<?php $lang = GO::language()->getLanguage(); ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<?php GO::router()->getController()->fireEvent('headstart'); ?>	
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true"/> 

	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<meta name="robots" content="noindex" />
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?php echo \GO::config()->title; ?>">
	<?php
	if(!empty(\GO::config()->favicon)) {
		echo '<link href="'.\GO::config()->favicon.'" rel="shortcut icon" type="image/x-icon">';
	} else {
	?>
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/favicon-16x16.png">
	<link rel="manifest" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/site.webmanifest">
	<link rel="mask-icon" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/safari-pinned-tab.svg" color="#888888">
	<?php } ?>
	
	<meta name="msapplication-TileColor" content="#2b5797">
	<meta name="theme-color" content="#ffffff">

	<title><?php echo \GO::config()->title; ?></title>
	
	<link href="<?=\GO::view()->getUrl()?>themes/Paper/style.css?v=<?=\GO()->getVersion(); ?>" media="screen and (min-device-width:1201px)" type="text/css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" media="screen and (max-device-width:1200px)" href="<?=\GO::view()->getUrl()?>themes/Paper/style-mobile.css?v=<?=\GO()->getVersion(); ?>" />
	<link href="<?=\GO::view()->getUrl()?>css.php?v=<?=\GO()->getVersion(); ?>" type="text/css" rel="stylesheet" />
	
	<?php
	if(!empty(\GO::config()->custom_css_url))
		echo '<link href="'.\GO::config()->custom_css_url.'" type="text/css" rel="stylesheet" />';
	//$this is \GO\Core\Controller\Auth
	\GO::router()->getController()->fireEvent('head');
	
	?>
	<style>
	<?php
		if(GO()->getSettings()->primaryColor) {
	?>
		:root {
				--c-primary: <?php echo '#'.GO()->getSettings()->primaryColor; ?>;
				--c-primary-tp: <?php echo GO()->getSettings()->getPrimaryColorTransparent(); ?>;
		}
	<?php
			if(GO()->getSettings()->logoId) {
				echo ".go-app-logo, #go-logo {background-image: url(".\go\core\fs\Blob::url(GO()->getSettings()->logoId).")}";
			}
		}
	?>
	</style>
</head>
<body>
	<div id="sound"></div>
	<!--Putting scripts in div will speed up developer tools because they don't have to show all those nodes-->
	<div id="scripts-container">
		<?php require(\GO::config()->root_path.'views/Extjs3/default_scripts.inc.php'); ?>
		<script type="text/javascript">GO.util.density = GO.util.isMobileOrTablet() ? 160 : 140;</script>
	</div>
</body>
</html>
