<?php
function smarty_function_force_url($params, &$smarty) {
	global $co, $GO_MODULES;

	if($co->site['enable_rewrite']!='1')
	{
		//Only do this when we are not in testing mode

		$https = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == "1");
		$full_url = 'http';
		if ($https) {
			$full_url .= 's';
		}

		$full_url .=  '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		if(substr($params['url'],-1,1)!='/')
		{
			$params['url'].='/';
		}

		$new_full_url = preg_replace('/.*:\/\/[^\/]*\//', $params['url'], $full_url);

		//echo $full_url.'<br>'.$new_full_url;

		if($new_full_url!=$full_url) {
			header('Location: '.$new_full_url);
			exit();
		}
	}

	return $url;
}
