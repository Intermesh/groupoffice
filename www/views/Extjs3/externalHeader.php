<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<link href="<?php echo \GO::config()->host; ?>views/Extjs3/themes/Default/external.css" type="text/css" rel="stylesheet" />
		<?php
		
		$themesUrl = \GO::config()->host."views/Extjs3/themes/";
		$themesPath = \GO::config()->root_path."views/Extjs3/themes/";
		$theme = \GO::user() ? \GO::user()->theme : \GO::config()->theme;
		
		if(!empty(\GO::config()->favicon))
		{
			$favicon = \GO::config()->favicon;
		}else
		{
			$favicon = $themesPath.$theme.'/images/groupoffice.ico';
			if(!file_exists($favicon))
				$favicon = $themesUrl.'Default/images/groupoffice.ico';
			else
				$favicon = $themesUrl.$theme.'/images/groupoffice.ico';
		}
		
		
		
		if($theme!='Default' && file_exists(\GO::config()->root_path."views/Extjs3/themes/".$theme."/external.css")){
			?>
			<link href="<?php echo \GO::config()->host; ?>views/Extjs3/themes/<?php echo $theme; ?>/external.css" type="text/css" rel="stylesheet" />
			<?php
		}
		if(!empty(\GO::config()->custom_css_url))
			echo '<link href="'.\GO::config()->custom_css_url.'" type="text/css" rel="stylesheet" />';
		
		if(\GO::modules()->isInstalled("customcss") && file_exists(\GO::config()->file_storage_path.'customcss/style.css'))
			echo '<style>'.file_get_contents(\GO::config()->file_storage_path.'customcss/style.css').'</style>'."\n";
		?>
			<link href="<?php echo $favicon; ?>" rel="shortcut icon" type="image/x-icon">
		<title><?php echo \GO::config()->title; ?></title>
		
		<?php
		if(isset($head)){
			echo $head;
		}
		?>
	</head>
<body>
	<div id="container">
