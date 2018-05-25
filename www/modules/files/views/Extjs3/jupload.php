<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: jupload.php 7752 2011-07-26 13:48:43Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */
extract($data);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
<!--		<link href="<?php echo \GO::config()->host; ?>views/Extjs3/themes/Default/external.css" type="text/css" rel="stylesheet" />-->
		<title><?php echo \GO::config()->title; ?></title>
	</head>
	
	<?php echo $afterUploadScript; ?>
	
<body>
	<div id="container">
	<?php echo $applet; ?>
	</div>
</body>
</html>
