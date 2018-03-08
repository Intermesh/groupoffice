<?php
require_once('../../Group-Office.php');

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
	$username = ($_POST['username']);
	$password = ($_POST['password']);

	try{

		require_once($GO_CONFIG->class_path.'base/auth.class.inc.php');
		$GO_AUTH = new GO_AUTH();

		if (!$GO_AUTH->login($username, $password))
		{
			$smarty->assign('failed', true);
		}else {

			if (isset($_POST['remind']))
			{
				require_once($GO_CONFIG->class_path.'cryptastic.class.inc.php');
				$c = new cryptastic();

				SetCookie("GO_UN",$c->encrypt($username),time()+3600*24*30,"/",'',!empty($_SERVER['HTTPS']),true);
				SetCookie("GO_PW",$c->encrypt($password),time()+3600*24*30,"/",'',!empty($_SERVER['HTTPS']),true);
			}

			if(strpos($success_url, 'login.php') || strpos($success_url, 'logout.php')){
				require_once($GO_MODULES->modules['cms']['path'].'smarty_plugins/function.cms_href.php');
				$success_url = str_replace('&amp;', '&', smarty_function_cms_href(array('path'=>''), $smarty));
			}

			header('Location: '.$success_url);
			exit();
		}
	}
	catch(Exception $e){
		$smarty->assign('failed', true);
	}
}
echo $co->replace_urls($smarty->fetch('auth/login.tpl'));
