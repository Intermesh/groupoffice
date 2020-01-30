<?php
extract($data);

$module = isset($m) && preg_match('/[a-z]+/', $m) ? $m : '';
$function = isset($f) && preg_match('/[a-z]+/i', $m) ? $f : '';
$funcParams = isset($p) ? $p : '';

//if(strpos($_SERVER['QUERY_STRING'], '<script') || strpos(urldecode($_SERVER['QUERY_STRING']), '<script'))
//				die('Invalid request');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo \GO::config()->product_name; ?></title>
<script type="text/javascript">

	window.name = "go_launcher";
function launchGO(){
	// var win = window.open('', "groupoffice");

	console.warn(window.name);
	var win = window.open('', "<?php echo \GO::getId(); ?>");

	if(win && win.GO && win.GO.<?php echo $module; ?>)
	{
		win.GO.<?php echo $module; ?>.<?php echo $function; ?>.call(this, <?php echo json_encode($funcParams); ?>);
		win.focus();
	}else
	{

	     if(!win || win.closed || win.closed == 'undefined') {
           alert("Your browser is blocking popups. Please allow this site to create a popups.");
           win = self;
        }
		//the parameters will be handled in default_scripts.inc.php
		<?php
		\GO::setAfterLoginUrl(\GO::createExternalUrl($module,$function, $funcParams, true));
		?>
		self.location.href="<?php echo \GO::config()->host; ?>";
	}

	//self.close();
	win.focus();

}
</script>
</head>

<body onload="launchGO();" style="font:12px arial">
<h1><?php echo \GO::config()->product_name; ?></h1>
<?php
echo str_replace('{FUNCTION}', $module.'.'.$function.'()', \GO::t("Group-Office was already started. The requested screen is loaded in Group-Office. You can close this window or tab now and continue working in Group-Office."));
?>
</body>

</html>
