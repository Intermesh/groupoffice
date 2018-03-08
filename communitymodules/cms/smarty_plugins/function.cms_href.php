<?php
function smarty_function_cms_href($params, &$smarty) {
	global $co, $GO_MODULES;

	if(!isset($params['path']) && isset($params['folder_id'])) {
		$params['path']=$co->build_path($params['folder_id'], true, $co->site['root_folder_id']);
	}

	if(!isset($params['path'])) {
		$params['path']='';
	}

	if(!isset($params['params']))
		 $params['params']='';

	return $co->create_href_by_path($params['path'],  $params['params']);
}
