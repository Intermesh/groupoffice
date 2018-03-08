<?php

function smarty_function_files($params, &$smarty)
{	
	global $co, $GO_CONFIG, $GO_MODULES;
	
	require_once($GLOBALS['GO_MODULES']->modules['files']['class_path'].'files.class.inc.php');
	$fsdb = new files();

	if(empty($params['path']))
	{
		$images_path = $smarty->_tpl_vars['images_path'];
		
		$path = $images_path.$co->build_path($co->folder['id'], false, $co->site['root_folder_id']).'/'.$co->file['name'];
		
		if(!is_dir($path))
		{
			return '';//Could not find path: '.$path;
		}		
	}else
	{
		//$path = $GLOBALS['GO_CONFIG']->file_storage_path.$params['path'];
		$path = $params['path'];
	}

	if(empty($params['template']))
	{
		return 'No template specified in files function!';
	}

	$fs = new filesystem();
	$files = $fs->get_files_sorted($path);

	$html = '';
	
	$item_name = isset($params['item_name']) ? $params['item_name'] : 'file'; 

	$uneven=true;
	$s = new cms_smarty($co);
	for($i=0;$i<count($files);$i++)
	{
		$files[$i]['friendly_name']=str_replace('_', ' ', File::strip_extension($files[$i]['name']));
		$files[$i]['relpath']=$fsdb->strip_server_path($files[$i]['path']);
		
		$s->assign('index', $i);
		$s->assign($item_name, $files[$i]);
		$s->assign('even', $uneven ? 'uneven' : 'even');
		
		$html .= $s->fetch($params['template']);
		
		$uneven=!$uneven;
	}
	
	return $html;

}
?>
