<?php
function smarty_function_guestbook($params, &$smarty)
{
	global $co, $GO_LANGUAGE, $lang;
	
	require($GO_LANGUAGE->get_language_file('cms'));
	
	
	
	$items_params['root_path'] = isset($params['root_path']) ? $params['root_path'] : '';
	$params['root_folder_id'] = $items_params['root_folder_id']= isset($params['root_folder_id']) ? $params['root_folder_id'] : null;
	
	
	if(!empty($items_params['root_path']))
	{
		$folder =  $co->resolve_url($items_params['root_path'], $co->site['root_folder_id']);
		if(!$folder)
		{
			return 'Couldn\'t resolve path: '.$root_path;
		}else
		{
			$params['root_folder_id']=$folder['id'];
		}
	}
	

	$task = isset($_POST['guestbook_task']) ? $_POST['guestbook_task'] : '';

	if($task == 'add')
	{
		$file['name'] = ($_POST['name']);
		$file['content'] = nl2br(strip_tags($_POST['content']));
		$file['folder_id']=$params['root_folder_id'];
		
		if(empty($_SESSION['antispam_answer']) || $_SESSION['antispam_answer'] != $_POST[$_SESSION['antispam_var']])
		{
			$feedback = $lang['cms']['antispam_fail'];	
		}elseif($file['name']=='' || $file['content']=='')
		{
			$feedback = $lang['common']['missingField'];	
		}elseif(preg_match('/http(s)?:\/\//',$file['content'])){
			go_debug($_POST);
			$feedback = 'We think your message was spam. If this is not true please contact the site owner.';
		}else
		{			
			go_debug($_POST);		
			
			$co->add_file($file, $co->site);
			
			unset($_POST['name'], $_POST['content']);
		}
	}
	
	$number1 = rand(1,5);
	$number2 = rand(1,5);	
	$ops=array('+','*');
	
	$sum = $number1.' '.$ops[array_rand($ops)].' '.$number2;	
	$smarty->assign('antispam_question',str_replace('*','x', $sum));
	eval('$_SESSION["antispam_answer"]='.$sum.';');
	
	$_SESSION['antispam_var']=uniqid(time());
	$smarty->assign('antispam_var',$_SESSION['antispam_var']);

	if(isset($feedback))
	{
		$smarty->assign('feedback', $feedback);
	}
	
	$items_params['item_template'] = isset($params['item_template']) ? $params['item_template'] : 'guestbook/guestbook_item.tpl';
	$items_params['max_items']=isset($params['max_items']) ? $params['max_items'] : 5;	
	$items_params['paging_id'] = isset($params['paging_id']) ? $params['paging_id'] : 'guestbook_pages';
	$items_params['root_folder_id']=$params['root_folder_id'];
	$items_params['wrap_div']='false';	
	$items_params['reverse']='true';
	$items_params['sort_time']=isset($params['sort_time']) ? $params['sort_time'] : '';
	
	
	$html = $smarty->fetch('guestbook/guestbook_form.tpl');
	
	$html .=  $co->print_items($items_params, &$smarty);

	return $html;
}
?>