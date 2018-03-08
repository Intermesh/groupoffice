<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<?php
//$this is \GO\Core\Controller\Auth
GO::router()->getController()->fireEvent('headstart');
?>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<meta name="robots" content="noindex" />
<meta http-equiv="x-ua-compatible" content="IE=10">
<?php
$favicon = !empty(\GO::config()->favicon) ? \GO::config()->favicon : \GO::view()->getTheme()->getUrl()."images/groupoffice.ico?";
?>
<link href="<?php echo $favicon; ?>" rel="shortcut icon" type="image/x-icon">
<title><?php echo \GO::config()->title; ?></title>
<?php
\GO::view()->addStylesheet(\GO::view()->getPath().'ext/resources/css/ext-all.css', \GO::view()->getUrl().'ext/resources/css/');
\GO::view()->addStylesheet(\GO::view()->getPath().'javascript/calendar/resources/css/calendar.css', \GO::view()->getUrl().'javascript/calendar/resources/css/');
\GO::view()->addStylesheet(\GO::view()->getPath().'themes/Default/xtheme-groupoffice.css', \GO::view()->getUrl().'themes/Default/');
\GO::view()->addStylesheet(\GO::view()->getPath().'themes/Default/style.css', \GO::view()->getUrl().'themes/Default/');
\GO::view()->loadModuleStylesheets();
?>
<link href="<?php echo \GO::view()->getCachedStylesheet(); ?>" type="text/css" rel="stylesheet" />
<?php
if(!empty(\GO::config()->custom_css_url))
	echo '<link href="'.\GO::config()->custom_css_url.'" type="text/css" rel="stylesheet" />';

//$this is \GO\Core\Controller\Auth
GO::router()->getController()->fireEvent('head');

?>


</head>
<body>