<?php
if(is_dir($GLOBALS['GO_CONFIG']->module_path.'mailings'))
{
	$module=array();
	$module['version']='0';
	$module['id']='mailings';
	$module['sort_order'] = count($GLOBALS['GO_MODULES']->modules)+1;

	$GLOBALS['GO_MODULES']->load_modules();

	if(isset($GLOBALS['GO_MODULES']->modules['users']['acl_id'])){
		$module['acl_id']=$GLOBALS['GO_SECURITY']->get_new_acl();
		$GLOBALS['GO_SECURITY']->copy_acl($GLOBALS['GO_MODULES']->modules['addressbook']['acl_id'], $module['acl_id']);
	}else
	{
		$module['acl_read']=$GLOBALS['GO_SECURITY']->get_new_acl();
		$module['acl_write']=$GLOBALS['GO_SECURITY']->get_new_acl();

		$GLOBALS['GO_SECURITY']->copy_acl($GLOBALS['GO_MODULES']->modules['addressbook']['acl_read'], $module['acl_read']);
		$GLOBALS['GO_SECURITY']->copy_acl($GLOBALS['GO_MODULES']->modules['addressbook']['acl_write'], $module['acl_write']);
	}
	
	
	
	$db->insert_row('go_modules', $module);

	$GLOBALS['GO_MODULES']->load_modules();

	$RERUN_UPDATE=true;

}
?>