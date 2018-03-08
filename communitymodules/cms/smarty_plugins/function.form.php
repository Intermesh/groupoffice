<?php
function smarty_function_form($params, &$smarty)
{
	global $co, $GO_LANGUAGE, $lang, $GO_CONFIG;
	
	require($GO_LANGUAGE->get_language_file('cms'));
	
	$form_id = $params['form_id'];
	$templates=explode(',', $params['templates']);	
	$template_index = isset($_POST['template_index']) ? $_POST['template_index'] : 0;
	
	if($_SERVER['REQUEST_METHOD']=='POST')
	{
		if(isset($_POST['required']))
		{
			foreach($_POST['required'] as $required_field)
			{
				 $required_field=substr($required_field, strlen($form_id)+1,-1);
				
				if(empty($_POST[$form_id][$required_field]))
				{
					$feedback = $lang['common']['missingField'];
				}
			}
		}
		
		if(!isset($feedback))
		{
			$_SESSION['cms']['forms'][$form_id]=isset($_SESSION['cms']['forms'][$form_id]) ? $_SESSION['cms']['forms'][$form_id] : array();
			
			$template_index++;
			
			$_SESSION['cms']['forms'][$form_id]=array_merge($_SESSION['cms']['forms'][$form_id], $_POST[$form_id]);
			
			if($template_index==(count($templates)-1))
			{
				require_once($GO_CONFIG->class_path.'mail/GoSwift.class.inc.php');
				
				//send
				$body='';
				foreach($_SESSION['cms']['forms'][$form_id] as $name=>$value)
				{
					$body .= str_replace('_', ' ',$name).":\t\t\t".($value)."\n";
				}
				
				$swift = new GoSwift($params['mail_to'], $params['mail_subject'], 0,0, 3, $body);
				$swift->set_from($co->site['name'], $co->site['webmaster_email']);
				$success = $swift->sendmail();
				unset($_SESSION['cms']['forms'][$form_id]);
				$smarty->assign('send_success', $success);
				
			}
		}else
		{
			$smarty->assign('feedback', $feedback);		
		}
	}
	
	$smarty->assign('form_id', $form_id);
	$smarty->assign('template_index', $template_index);

	$html = $smarty->fetch($templates[$template_index]);

	return $html;
}
?>