<?php
function smarty_function_include_file($params, &$smarty)
{
	global $co;

	if(!empty($params['path']))
	{
		$file = $co->resolve_url($params['path'], $co->site['root_folder_id']);
		
		if(!$file && empty($params['suppress_error']))
			return 'Path not found: '.$params['path'];
		else
			return $file['content'];
	}elseif(empty($params['file_id']))
	{
		return $lang['cms']['include_file_error'];
	}else
	{
		$file = $co->get_file($params['file_id']);
		return $file['content'];
	}
}
?>