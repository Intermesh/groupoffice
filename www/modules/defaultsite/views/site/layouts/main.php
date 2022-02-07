<?php
\Site::scripts()->registerGapiScript('jquery');
\Site::scripts()->registerGapiScript('jquery-ui');
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	
		<title>Group Office - <?php echo \Site::controller()->getPageTitle(); ?></title>
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo \Site::template()->getUrl(); ?>favicon.ico">
		<link rel="stylesheet" href="<?php echo \Site::template()->getUrl(); ?>css/site.css">
		
		<?php if(\Site::fileExists('style.css', false)){ ?>
		<link rel="stylesheet" href="<?php echo \Site::file('style.css', false); ?>">
		<?php } ?>
	
	</head>

	<body>
		<?php echo $content; ?>
	</body> 
</html>
