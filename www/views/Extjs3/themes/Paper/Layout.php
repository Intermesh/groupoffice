<!DOCTYPE html>
<html>
<head>
	<?php GO::router()->getController()->fireEvent('headstart'); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="HandheldFriendly" content="true"/> 

	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<meta name="robots" content="noindex" />
	<meta http-equiv="x-ua-compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="Group-Office">
	
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/favicon-16x16.png">
	<link rel="manifest" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/site.webmanifest">
	<link rel="mask-icon" href="<?php echo \GO::view()->getUrl().'themes/Paper/img/favicon'; ?>/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#2b5797">
	<meta name="theme-color" content="#ffffff">


	<title><?php echo \GO::config()->title; ?></title>
	<?php
		\GO::view()->addStylesheet(\GO::view()->getPath().'themes/Paper/flags/flags.min.css', \GO::view()->getUrl().'themes/Paper/flags/');
		\GO::view()->loadModuleStylesheets('Paper');
	?>
	<link href="<?=\GO::view()->getUrl()?>themes/Paper/style.css" media="screen and (min-device-width:1201px)" type="text/css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" media="screen and (max-device-width:1200px)" href="<?=\GO::view()->getUrl()?>themes/Paper/style-mobile.css" />
	<link href="<?php echo \GO::view()->getCachedStylesheet(); ?>" type="text/css" rel="stylesheet" />
	<?php
	if(!empty(\GO::config()->custom_css_url))
		echo '<link href="'.\GO::config()->custom_css_url.'" type="text/css" rel="stylesheet" />';
	//$this is \GO\Core\Controller\Auth
	\GO::router()->getController()->fireEvent('head');
	
	?>
	<style>
		#bg {
			background-image: url('<?=\GO::view()->getTheme()->getUrl()?>/img/bg/office-desk.jpg');
		}
		
		<?php
		if(GO()->getSettings()->primaryColor) {
			?>
		
			:root {
					--c-primary: <?php echo '#'.GO()->getSettings()->primaryColor; ?>;
					--c-primary-tp: <?php echo GO()->getSettings()->getPrimaryColorTransparent(); ?>;
			}
			<?php
		}
		?>
	</style>
</head>
<body>
	<div id="sound"></div>

	<?php require(\GO::config()->root_path.'views/Extjs3/default_scripts.inc.php'); ?>

			<script type="text/javascript">GO.util.density = GO.util.isMobileOrTablet() ? 160 : 140;</script>
</body>
</html>
