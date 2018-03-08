<?php
function smarty_function_go_authenticate($params, &$smarty)
{
	global $GO_MODULES, $GO_SECURITY, $co;

	if(!$GO_SECURITY->logged_in())
	{
		$url = $GO_MODULES->modules['cms']['url'].'login.php?site_id='.$co->site['id'].'&success_url='.urlencode($_SERVER['REQUEST_URI']);
		if(!empty($co->file['id']))
		{
			$url .= '&file_id='.$co->file['id'];
		}
		header('Location: '.$url);
		exit();
	}
}
