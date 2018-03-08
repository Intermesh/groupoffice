<?php
require('../../Group-Office.php');

require_once($GO_MODULES->modules['cms']['class_path'].'cms.class.inc.php');
require_once($GO_MODULES->modules['cms']['class_path'].'output.class.inc.php');
require_once($GO_MODULES->modules['cms']['class_path'].'cms_smarty.class.inc.php');
$cms = new cms();
$co = new cms_output();

if(!empty($_REQUEST['file_id']))
{
	$co->set_by_id($_REQUEST['file_id'], 0);
}else
{
	$co->load_site();
}

$smarty = new cms_smarty($co);


$success_url=isset($_REQUEST['success_url'])  ? ($_REQUEST['success_url']) : $cancel_url;

$smarty->assign('success_url', $success_url);

$GO_SECURITY->logout();
$smarty->assign('session', array());

echo $co->replace_urls($smarty->fetch('auth/logout.tpl'));
