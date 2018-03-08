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

$cancel_url = isset($_REQUEST['cancel_url'])  ? ($_REQUEST['cancel_url']) : $_SERVER['HTTP_REFERER'];
$success_url=isset($_REQUEST['success_url'])  ? ($_REQUEST['success_url']) : $cancel_url;

$smarty->assign('cancel_url', $cancel_url);
$smarty->assign('success_url', $success_url);

if($_SERVER['REQUEST_METHOD']=='POST')
{
	require_once($GO_CONFIG->class_path.'base/users.class.inc.php');
	$GO_USERS = new GO_USERS();

	if($_POST['email'] == '')
	{		
		$smarty->assign('feedback', $co->config['lang']['missing_field']);
	}elseif($user = $GO_USERS->get_user_by_email(($_POST['email'])))
	{
		$new_password = $GO_USERS->random_password();
		
		$up_user['id']=$user['id'];
		$up_user['password']=$new_password;
		
		$GO_USERS->update_profile($up_user);

		$salutation = $lang['common']['default_salutation'][$user['sex']];
		if(!empty($user['middle_name']))
			$salutation .= ' '.$user['middle_name'];
		$salutation .= ' '.$user['last_name'];
		
		$smarty->assign('salutation', $salutation);
		$smarty->assign('username', $user['username']);
		$smarty->assign('password', $new_password);

		$mail_body = $smarty->fetch('auth/lost_password_email.tpl');
		
		require_once($GO_CONFIG->class_path.'mail/GoSwift.class.inc.php');
		$swift = new GoSwift($user['email'], $co->config['lang']['lost_password_subject']);
		$swift->set_from($co->site['webmaster'], $co->site['name']);
		$swift->set_body($mail_body);
		$swift->sendmail();
		
		
		
		$smarty->assign('success', true);
	}else
	{
		$smarty->assign('feedback', $co->config['lang']['lost_password_email_not_found']);
	}

}
echo $co->replace_urls($smarty->fetch('auth/lost_password.tpl'));
