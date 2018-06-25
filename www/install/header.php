
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="style.css" rel="stylesheet" type="text/css"/>
		<title>Group-Office installation </title>
	</head>
	<body>

		<header>
			<img src="../views/Extjs3/themes/Paper/img/logo-white.svg" /> <br />
			<small>Installation</small>
			
		</header>
		
		
		<?php
		
		
		if(is_dir("/etc/groupoffice/" . $_SERVER['HTTP_HOST'])) {	
			echo "<section><fieldset>";		
			echo("A config folder was found in /etc/groupoffice/" . $_SERVER['HTTP_HOST'] .". Please move all your domain configuration folders from /etc/groupoffice/* into /etc/groupoffice/multi_instance/*. Only move folders, leave /etc/groupoffice/config.php and other files where they are.");
			echo "</fieldset></section>";
		
			require('footer.php');
			exit();
		}		
		
		?>
