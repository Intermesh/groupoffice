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

	require_once($GO_CONFIG->class_path.'base/groups.class.inc.php');
	$GO_GROUPS = new GO_GROUPS();

	function check_fields($required_fields)
	{

		foreach($required_fields as $field)
		{
			if(!empty($field) && empty($_POST[$field]))
			{
				return false;
			}
		}

		return true;
	}

	require($GO_LANGUAGE->get_language_file('users'));


	$fields = explode(',', 'sex,address,home_phone,cellular,company,department,function');

	$modules_read = array_map('trim', explode(',',$GO_CONFIG->register_modules_read));
	$modules_write = array_map('trim', explode(',',$GO_CONFIG->register_modules_write));

	//user groups the user will be added to.
	$user_groups = $GO_GROUPS->groupnames_to_ids(array_map('trim',explode(',',$GO_CONFIG->register_user_groups)));

	//user groups that this user will be visible to
	$visible_user_groups = $GO_GROUPS->groupnames_to_ids(array_map('trim',explode(',',$GO_CONFIG->register_visible_user_groups)));


	$user['first_name'] = isset($_POST['first_name']) ?  (trim($_POST['first_name'])) : '';
	$user['middle_name'] = isset($_POST['middle_name']) ?  (trim($_POST['middle_name'])) : '';
	$user['last_name'] = isset($_POST['last_name']) ?  (trim($_POST['last_name'])) : '';

	if(in_array('title_initials', $fields))
	{
		$user['initials'] = isset($_POST['initials']) ? ($_POST["initials"]) : '';
		$user['title'] = isset($_POST['title']) ? ($_POST["title"]) : '';
	}
	if(in_array('birthday', $fields))
	{
		$user['birthday'] = isset($_POST['birthday']) ? ($_POST["birthday"]) : '';
	}
	$user['email'] = isset($_POST['email']) ? ($_POST["email"]) : '';
	if(in_array('home_phone', $fields))
	{
		$user['home_phone'] = isset($_POST['home_phone']) ? ($_POST["home_phone"]) : '';
	}
	if(in_array('work_phone', $fields))
	{
		$user['work_phone'] = isset($_POST['work_phone']) ? ($_POST["work_phone"]) : '';
	}
	if(in_array('fax', $fields))
	{
		$user['fax'] = isset($_POST['fax']) ? ($_POST["fax"]) : '';
	}
	if(in_array('work_fax', $fields))
	{
		$user['work_fax'] = isset($_POST['work_fax']) ? ($_POST["work_fax"]) : '';
	}
	if(in_array('cellular', $fields))
	{
		$user['cellular'] = isset($_POST['cellular']) ? ($_POST["cellular"]) : '';
	}
	if(in_array('address', $fields))
	{
		$user['country'] = isset($_POST['country']) ? ($_POST["country"]) : $GO_CONFIG->default_country;
		$user['state'] = isset($_POST['state']) ? ($_POST["state"]) : '';
		$user['city'] = isset($_POST['city']) ? ($_POST["city"]) : '';
		$user['zip'] = isset($_POST['zip']) ? ($_POST["zip"]) : '';
		$user['address'] = isset($_POST['address']) ? ($_POST["address"]) : '';
		$user['address_no'] = isset($_POST['address_no']) ? ($_POST["address_no"]) : '';
	}

	if(in_array('work_address', $fields))
	{
		$user['work_country'] = isset($_POST['work_country']) ? ($_POST["work_country"]) : $GO_CONFIG->default_country;
		$user['work_state'] = isset($_POST['work_state']) ? ($_POST["work_state"]) : '';
		$user['work_city'] = isset($_POST['work_city']) ? ($_POST["work_city"]) : '';
		$user['work_zip'] = isset($_POST['work_zip']) ? ($_POST["work_zip"]) : '';
		$user['work_address'] = isset($_POST['work_address']) ? ($_POST["work_address"]) : '';
		$user['work_address_no'] = isset($_POST['work_address_no']) ? ($_POST["work_address_no"]) : '';
	}

	if(in_array('company', $fields))
	{
		$user['company'] = isset($_POST['company']) ? ($_POST["company"]) : '';
	}
	if(in_array('department', $fields))
	{
		$user['department'] =  isset($_POST['department']) ? ($_POST["department"]) : '';
	}
	if(in_array('function', $fields))
	{
		$user['function'] =  isset($_POST['function']) ? ($_POST["function"]) : '';
	}
	if(in_array('sex', $fields))
	{
		$user['sex'] = isset($_POST['sex']) ? ($_POST["sex"]) : 'M';
	}

	if(in_array('homepage', $fields))
	{
		$user['homepage'] = isset($_POST['homepage']) ? ($_POST["homepage"]) : '';
	}

	$user['language'] = isset($_POST['SET_LANGUAGE']) ? $_POST['SET_LANGUAGE'] : $GO_LANGUAGE->language['code'];

	$user['theme'] = $GO_CONFIG->theme;
	$user['username'] = isset($_POST['username']) ? ($_POST['username']) : '';
	$user['enabled'] = '1';


	$login_task = isset($_REQUEST['login_task']) ? $_REQUEST['login_task'] : '';
	$goto_url = isset($_REQUEST['goto_url']) ? ($_REQUEST['goto_url']) : $_SERVER['PHP_SELF'];

	$birthday = isset($_REQUEST['birthday']) ? $_REQUEST['birthday'] : '';



	$required_registration_fields='address';

	$required_registration_fields = str_replace('address', 'address,address_no,zip,city,state,country', $required_registration_fields);
	$required_registration_fields = str_replace('work_address', 'work_address,work_address_no,work_zip,work_city,work_state,work_country', $required_registration_fields);
	$required_registration_fields = str_replace('title_initials', 'title,initials', $required_registration_fields);
	$required_fields = explode(',',$required_registration_fields);

	$required_fields[]='username';
	$required_fields[]='email';
	$required_fields[]='first_name';
	$required_fields[]='last_name';


	//if($GO_CONFIG->auto_activate_accounts)
	//{
		$pass1 = ($_POST["pass1"]);
		$pass2 = ($_POST["pass2"]);
		$user['password'] = ($_POST["pass1"]);
	/*}else {
		$user['password']='';
	}*/

	//$user = array_map('addslashes',$user);

	if (!check_fields($required_fields) || ($GO_CONFIG->auto_activate_accounts && (empty($pass1) || empty ($pass2))))
	{
		$feedback = $lang['common']['missingField'];
	}elseif(!$GO_USERS->check_username($user['username']))
	{
		$feedback = $lang['users']['error_username'];
	}elseif(!String::validate_email($user['email']))
	{
		$feedback = $lang['users']['error_email'];
	}elseif($GO_USERS->get_user_by_username($user['username']))
	{
		$feedback = $lang['users']['error_username_exists'];
	}elseif(!$GO_CONFIG->allow_duplicate_email && $GO_USERS->email_exists($user['email']))
	{
		$feedback = $lang['users']['error_email_exists'];
	}elseif($GO_CONFIG->auto_activate_accounts && $pass1 != $pass2)
	{
		$feedback = $lang['users']['error_match_pass'];
	}else
	{
		if(isset($_POST['birthday']))
		{
			$user['birthday'] = date_to_db_date($_POST['birthday']);
		}

		if ($new_user_id = $GO_USERS->add_user($user, $user_groups, $visible_user_groups, $modules_read, $modules_write	))
		{
			$salutation = $lang['common']['default_salutation'][$user['sex']];
			if(!empty($user['middle_name']))
				$salutation .= ' '.$user['middle_name'];
			$salutation .= ' '.$user['last_name'];
			
			$smarty->assign('salutation', $salutation);
			$smarty->assign('username', $user['username']);
			$smarty->assign('password', $user['password']);
	
			$mail_body = $smarty->fetch('auth/register_email.tpl');
			
			require_once($GO_CONFIG->class_path.'mail/GoSwift.class.inc.php');
			$swift = new GoSwift($user['email'], $co->config['lang']['register_subject']);
			$swift->set_from($co->site['webmaster'], $co->site['name']);
			$swift->set_body($mail_body);
			$swift->sendmail();

			require_once($GO_CONFIG->class_path.'base/auth.class.inc.php');
			$GO_AUTH = new GO_AUTH();
			
			$GO_AUTH->login($user['username'], $user['password']);
			
			$smarty->assign('success', true);
			
		}else
		{
			$error = $registration_failure;
		}
	}


}

require($GO_LANGUAGE->get_base_language_file('countries'));
asort($countries);
$smarty->assign('countries', $countries);

$country = isset($_POST['country']) ? $_POST['country'] : strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
$smarty->assign('country', $country);

if(isset($feedback))
$smarty->assign('feedback', $feedback);

echo $co->replace_urls($smarty->fetch('auth/register.tpl'));
