<?php
function smarty_function_get_file($params, &$smarty)
{
	global $co;

	if(!empty($params['path']))
	{
		$file = $co->resolve_url($params['path'], $co->site['root_folder_id']);

		if(!$file && empty($params['suppress_error']))
			echo 'Path not found: '.$params['path'];
		else {
			$file = $co->get_file($file['id']);
			$smarty->assign($params['var_name'],$file);
		}
	}elseif(empty($params['file_id']))
	{
		echo $lang['cms']['include_file_error'];
	}else
	{
		$file = $co->get_file($params['file_id']);
		$smarty->assign($params['var_name'],$file);
	}
}
?>