<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: action.php 8268 2011-10-10 15:19:45Z wilmar1980 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
require_once("../../go3compat/Group-Office.php");
$GO_SECURITY->json_authenticate('cms');
require_once ($GO_MODULES->modules['cms']['class_path']."cms.class.inc.php");
//require_once ($GO_LANGUAGE->get_language_file('cms'));
$cms = new cms();

try {
	switch($_REQUEST['task']) {
		case 'delete':

			$delete_items = json_decode(($_POST['delete_items']), true);
			$response['deleted_nodes']=array();
			foreach($delete_items as $delete_item) {
				$item = explode('_', $delete_item);

				if($item[0]=='folder') {
					$folder = $cms->get_folder(($item[1]));

					if($folder['parent_id']==0)
						throw new Exception($lang['cms']['cant_delete_site_treeview']);

					$site = $cms->get_site($folder['site_id']);
					if(!$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']))
						throw new AccessDeniedException();

					$cms->delete_folder($folder['id']);

				}elseif($item[0]=='file') {
					$file = $cms->get_file(($item[1]));
					$folder = $cms->get_folder($file['folder_id']);
					$site = $cms->get_site($folder['site_id']);

					if(!$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']))
						throw new AccessDeniedException();

					$cms->delete_file($file['id']);
				}

				$response['deleted_nodes'][]=$delete_item;
			}

			$response['success']=true;

			break;

		case 'save_site':
			if(!$GO_MODULES->modules['cms']['write_permission']) {
				throw new AccessDeniedException();
			}

			$site_id=$site['id']=isset($_POST['site_id']) ? ($_POST['site_id']) : 0;

			if(isset($_POST['user_id']))
				$site['user_id']=$_POST['user_id'];

			$site['domain']=$_POST['domain'];
			$site['webmaster']=$_POST['webmaster'];
			$site['language']=$_POST['language'];
			$site['template']=$_POST['template'];
			$site['name']=$_POST['name'];
			$site['enable_categories']= !empty($_POST['enable_categories']) ? '1' : '0';
			$site['enable_rewrite']=isset($_POST['enable_rewrite']) ? '1' :'0';

			if(isset($_POST['rewrite_base'])){
				if(substr($_POST['rewrite_base'],-1)!='/'){
					$_POST['rewrite_base'].='/';
				}
				$site['rewrite_base']=$_POST['rewrite_base'];
			}

			if($site['id']>0) {
				$cms->update_site($site);
				$response['success']=true;
			}else {
				$site['user_id']=$GO_SECURITY->user_id;

				$response['acl_write']=$site['acl_write']=$GO_SECURITY->get_new_acl('site');

				$site_id= $cms->add_site($site);

				$response['site_id']=$site_id;
				$response['success']=true;
			}
			break;

		case 'save_folder':

			$folder_id=$folder['id']=isset($_POST['folder_id']) ? ($_POST['folder_id']) : 0;

			/*
			$site = $cms->get_site((trim($_POST['site_id'])));
				
			if(!$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']))
			{
				throw new AccessDeniedException();
			}*/
			$folder['type']=isset($_POST['type']) ? $_POST['type'] : '';
			$folder['name']=$_POST['name'];
			$folder['disabled']=isset($_POST['disabled']) ? '1' : '0';
			$folder['feed']=isset($_POST['feed']) ? '1' : '0';

			if($folder['id']>0) {
				$old_folder = $cms->get_folder($folder['id']);
				$site = $cms->get_site($old_folder['site_id']);
			}else {
				$site = $cms->get_site(($_REQUEST['site_id']));
			}

			$template_options=array();
			$config = $cms->get_template_config($site['template']);
			while($type = array_shift($config['types'])) {
				if($folder['type']==$type[0]) {
					$template_options=$type[1];
					break;
				}
			}
			$template_values = array();
			foreach($template_options as $template_option) {
				$template_values[$template_option['name']] = isset($_POST[$template_option['name']]) ? ($_POST[$template_option['name']]) : '';
			}
			$folder['option_values']=  $cms->build_template_values_xml($template_values);
			$folder['default_template']=isset($_POST['default_template']) ? $_POST['default_template'] : '';

			if(!empty($folder_id) && isset($_POST['recursive'])) {
				/*$recursive_values=array();
				
				foreach($_POST['recursive'] as $template_option=>$dummy)
				{
					$recursive_values[$template_option] = isset($_POST[$template_option]) ? ($_POST[$template_option]) : '';					
				}	*/			
				$cms->apply_template_options_recursively($folder['type'], $folder['default_template'], $folder, $site);
			}

			if($folder['id']>0) {
				if(isset($_POST['authentication']) && empty($old_folder['acl']))
					$folder['acl']=$response['acl']=$GO_SECURITY->get_new_acl('cms');
				elseif(!isset($_POST['authentication']) && !empty($old_folder['acl'])) {
					$folder['acl']=$response['acl']=0;
					$GO_SECURITY->delete_acl($old_folder['acl']);
				}

				$cms->update_folder($folder);
				$response['success']=true;
			}else {

				if(isset($_POST['authentication']))
					$folder['acl']=$response['acl']=$GO_SECURITY->get_new_acl('cms');

				$folder['site_id']=$_POST['site_id'];
				$folder['parent_id']=$_POST['parent_id'];

				$folder_id= $cms->add_folder($folder);

				$response['folder_id']=$folder_id;
				$response['success']=true;
			}

			break;

		case 'copy':

			$destination_folder_id = ($_POST['destination_folder_id']);
			$folders = json_decode(($_POST['copy_folders']));
			$files = json_decode(($_POST['copy_files']));

			foreach($files as $file_id) {
				$cms->copy_file($file_id, $destination_folder_id);
			}

			foreach($folders as $folder_id) {
				if($cms->is_in_path($folder_id, $destination_folder_id)) {
					require_once($GO_LANGUAGE->get_language_file('cms'));
					throw new Exception($lang['cms']['cant_move_into_itself']);
				}else {
					$cms->copy_folder($folder_id, $destination_folder_id);
				}
			}

			$response['success']=true;

			break;

		case 'move_file':

			$file['id']=$_POST['file_id'];
			$file['folder_id']=$_POST['folder_id'];

			$folder = $cms->get_folder($file['folder_id']);
			$site = $cms->get_site($folder['site_id']);

			$cms->update_file($file,$site);

			$sort_order = json_decode($_POST['sort_order'],true);

			$up_folder=array();
			$up_file=array();

			foreach($sort_order as $item) {
				if($item['fstype']=='folder') {
					$up_folder['id']=$item['id'];
					$up_folder['priority']=$item['sort_order'];
					$cms->update_folder($up_folder);
				}else {
					$up_file['id']=$item['id'];
					$up_file['priority']=$item['sort_order'];
					$cms->update_file($up_file, $site);
				}
			}

			$response['success']=true;
			break;
		case 'move_folder':

			$folder['id']=$_POST['folder_id'];
			$folder['parent_id']=$_POST['parent_id'];

			$cms->update_folder($folder);

			$folder = $cms->get_folder($_POST['folder_id']);
			$parent = $cms->get_folder($_POST['parent_id']);
			$site = $cms->get_site($folder['site_id']);

			$sort_order = json_decode($_POST['sort_order'],true);
			$up_folder=array();
			$up_file=array();
			foreach($sort_order as $item) {
				if($item['fstype']=='folder') {
					$up_folder['id']=$item['id'];
					$up_folder['priority']=$item['sort_order'];
					$up_folder['site_id']=$parent['site_id'];
					$cms->update_folder($up_folder);
				}else {
					$up_file['id']=$item['id'];
					$up_file['priority']=$item['sort_order'];
					$cms->update_file($up_file,$site);
				}
			}

			$response['success']=true;
			break;

		case 'save_file':

			$file_id=$file['id']=isset($_POST['file_id']) ? ($_POST['file_id']) : 0;


			//$site = $cms->get_site((trim($_POST['site_id'])));

			/*if(!$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']))
			 {
				throw new AccessDeniedException();
				}*/

			$file['name']=$_POST['name'];
			$file['content']=$_POST['content'];
			$file['type']=isset($_POST['type']) ? $_POST['type'] : '';
			$file['auto_meta']=isset($_POST['auto_meta']) ? '1' : '0';
			$file['show_until']=Date::to_unixtime($_POST['show_until']);
			$file['sort_time']=Date::to_unixtime($_POST['sort_date']);

			// was $file['auto_meta']=='1'
			if(!empty($file['auto_meta'])) {
				$file['title']=$cms->get_title_from_html($file['content']);
				if(stripos($file['title'],$file['name'])===false) {
					$file['title'] = empty($file['title']) ? $file['name'] : $file['name'].' - '.$file['title'];
				}
				$file['keywords']=$cms->get_keywords_from_html($file['content']);
				if(stripos($file['keywords'],$file['name'])===false)
					$file['keywords'] = empty($file['keywords']) ? $file['name'] : $file['name'].', '.$file['keywords'];

				$file['description']=$cms->get_description_from_html($file['content']);

				$response['title']=$file['title'];
				$response['keywords']=$file['keywords'];
				$response['description']=$file['description'];
			}else {
				$file['title']=$_POST['title'];
				$file['description']=$_POST['description'];
				$file['keywords']=$_POST['keywords'];
			}

			if($file_id==0) {
				$file['folder_id']=$folder_id=$_POST['folder_id'];
			}else {
				$old_file = $cms->get_file($file_id);
				$folder_id=$old_file['folder_id'];
			}
			$folder = $cms->get_folder($folder_id);
			$site = $cms->get_site($folder['site_id']);

			if(!$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write'])) {
				throw new AccessDeniedException();
			}
			
			$config = $cms->get_template_config($site['template']);
			if($config){
				$template_options=array();
				while($type = array_shift($config['types'])) {
					if($file['type']==$type[0]) {
						$template_options=$type[1];
						break;
					}
				}
				$template_values = array();
				foreach($template_options as $template_option) {
					$template_values[$template_option['name']] = isset($_POST[$template_option['name']]) ? ($_POST[$template_option['name']]) : '';
				}
				$file['option_values']=  $cms->build_template_values_xml($template_values);
			}
			
			if($file['id']>0) {
				$cms->update_file($file, $site, $old_file);
				$response['success']=true;
			}else {
				$file_id= $cms->add_file($file, $site);

				$response['file_id']=$file_id;
				$response['files_folder_id']=$file['files_folder_id'];
				$response['success']=true;
			}
			break;
		case 'save_comment':

			$comment_id=$comment['id']=isset($_POST['comment_id']) ? ($_POST['comment_id']) : 0;

			$comment['file_id']=$_POST['file_id'];
			if(isset($_POST['user_id']))
				$comment['user_id']=$_POST['user_id'];
			$comment['name']=$_POST['name'];
			$comment['comments']=$_POST['comments'];
			if($comment['id']>0) {
				$cms->update_comment($comment);
				$response['success']=true;
			}else {
				$comment['user_id']=$GO_SECURITY->user_id;


				$comment_id= $cms->add_comment($comment);

				$response['comment_id']=$comment_id;
				$response['success']=true;
			}
		
		case 'subscribe':

			$user_id = $_POST['user_id'];
			$folder_id = $_POST['folder_id'];

			$response['success']=$cms->user_folder_allow($user_id, $folder_id);

			if(!$response['success']) {
				$response['feedback']=$lang['email']['feedbackSubscribeFolderFailed'];
			}
			break;

		case 'unsubscribe':

			$user_id = $_POST['user_id'];
			$folder_id = $_POST['folder_id'];

			$response['success']=$cms->user_folder_deny($user_id, $folder_id);

			if(!$response['success']) {
				$response['feedback']=$lang['email']['feedbackSubscribeFolderFailed'];
			}
			break;

		case 'enable_filter':

			$site_id = $_POST['site_id'];

			$response['success']=$cms->enable_filter($_POST['user_id'], $site_id);

			if(!$response['success']) {
				$response['feedback']=$lang['email']['feedbackSubscribeFolderFailed'];
			}
			break;

		case 'disable_filter':

			$site_id = $_POST['site_id'];

			$response['success']=$cms->disable_filter($_POST['user_id'], $site_id);

			if(!$response['success']) {
				$response['feedback']=$lang['email']['feedbackSubscribeFolderFailed'];
			}
			break;
		
		case 'update_category':
			
			$file_id = intval($_POST['file_id']);
			$file = $cms->get_file($file_id);
			$folder = $cms->get_folder($file['folder_id']);

			$category['id'] = intval($_POST['id']);
			$category['name'] = $_POST['name'];
			$category['site_id'] = $folder['site_id'];
			$category['parent_id'] = intval($_POST['parent_id']);
//			$used = !empty($_POST['checked']) ? '1' : '0';
			
			$cms->update_category($category);
			
//			$fc['category_id'] = $category['id'];
//			$fc['file_id'] = $file_id;
//			$cms->delete_file_category($fc);
//			
//			if ($used) {
//				$cms->add_file_category($fc);
//			}
			
			$response['success'] = true;
			break;
		
		case 'update_category_name':
			
			$category['id'] = intval($_POST['id']);
			$category['name'] = $_POST['name'];
			$cms->update_category($category);
					
			$response['success'] = true;
			break;
			
		case 'add_category':
			global $GO_LANGUAGE;
			
			$file_id = intval($_POST['file_id']);
			$file = $cms->get_file($file_id);
			$folder = $cms->get_folder($file['folder_id']);
			
			require_once($GO_LANGUAGE->get_language_file('cms'));
			
			$category['name'] = $lang['cms']['newCategory'];
			$category['site_id'] = $folder['site_id'];
			$category['parent_id'] = intval($_POST['parent_id']);
			
			$cms->add_category($category);
			
			$response['success'] = true;
			break;
			
		case 'assign_category':

			$file_id = intval($_POST['file_id']);
			$category_id = intval($_POST['category_id']);

			$response['success']=$cms->assign_file_to_category($file_id, $category_id);

			break;

		case 'unassign_category':

			$file_id = intval($_POST['file_id']);
			$category_id = intval($_POST['category_id']);

			$response['success']=$cms->unassign_file_from_category($file_id, $category_id);

			if(!$response['success']) {
				$response['feedback']=$lang['email']['feedbackSubscribeFolderFailed'];
			}
			break;
		/* {TASKSWITCH} */
	}
}catch(Exception $e) {
	$response['feedback']=$e->getMessage();
	$response['success']=false;
}
echo json_encode($response);