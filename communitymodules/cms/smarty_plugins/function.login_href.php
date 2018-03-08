<?php
function smarty_function_login_href($params, &$smarty)
{
	global $GO_MODULES, $GO_SECURITY, $co;

	$success_url = isset($params['success_url']) ? $params['success_url'] : $_SERVER['REQUEST_URI'];
	
	if(isset($params['success_url_params']))
	{
		$success_url.='&amp;'.$params['success_url_params'];
	}
	
	if(!$GO_SECURITY->logged_in())
	{
		$url = $GO_MODULES->modules['cms']['url'].'login.php?site_id='.$co->site['id'].'&amp;success_url='.urlencode($success_url);	
	}else
	{
		$url = $GO_MODULES->modules['cms']['url'].'logout.php?site_id='.$co->site['id'].'&amp;success_url='.urlencode($success_url);
		
	}
	if(!empty($co->file['id']))
	{
		$url .= '&amp;file_id='.$co->file['id'];
	}
	return $url;
}
?>