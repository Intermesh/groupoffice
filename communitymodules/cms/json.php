<?php
/** 
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: json.php 8326 2011-10-17 09:07:40Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

require('../../go3compat/Group-Office.php');
$GO_SECURITY->json_authenticate('cms');
require_once ($GO_MODULES->modules['cms']['class_path'].'cms.class.inc.php');
$cms = new cms();

function contains ($haystack, $needle) {
	foreach ($haystack as $i) {
		if ($i===$needle)
			return true;
	}
	return false;
}

function get_folder_tree($folder_id, $user_id) {

	$cms = new cms();
	$cms2 = new cms();

	$folder = $cms->get_folder($folder_id);
	$cms->get_folders($folder_id);

	$children = array();
	while ($child_folder = $cms->next_record()) {
		$children[] = get_folder_tree($child_folder['id'],$user_id);
	}

	return array(
					'text' => $folder['name'],
					'id' => 'folder_'.$folder['id'],
					'iconCls'=> $folder['disabled']=='1' ? 'cms-folder-disabled' : 'filetype-folder',
					'folder_id' => $folder['id'],
					'expanded' => true,
					'checked' => $cms2->has_folder_access($user_id,$folder_id),
					'canHaveChildren' => true,
					'children' => $children
	);
}

function create_tree($folder_id,$site,$path='',$filter_enabled) {
	global $GO_SECURITY;
	$cms = new cms();

	$response = array();

	$items = $cms->get_items($folder_id);

	while($item = array_shift($items)) {
		
		$path = empty($path) ? '' : $path.'/';
		
		if($item['fstype']=='file') {
			if (!$filter_enabled || $cms->has_folder_access($GO_SECURITY->user_id, $item['folder_id'])) {
				$response[] = array(
								'text'=>$item['name'],
								'id'=>'file_'.$item['id'],
								'fstype'=>'file',
								'iconCls'=>'filetype-html',
								'site_id'=>$site['id'],
								'file_id'=>$item['id'],
								'folder_id'=>$item['folder_id'],
								'template'=>$site['template'],
								'root_folder_id'=>$item['files_folder_id'],
								'leaf'=>true,
								'path'=> $path.urlencode($item['name'])
				);
			}
		} else {
			if (!$filter_enabled || $cms->has_folder_access($GO_SECURITY->user_id, $item['id'])) {
				$folderNode = array(
								'text'=>$item['name'],
								'fstype'=>'folder',
								'id'=>'folder_'.$item['id'],
								'iconCls'=> $item['disabled']=='1' ? 'cms-folder-disabled' : 'filetype-folder',
								'site_id'=>$site['id'],
								'folder_id'=>$item['id'],
								'template'=>$site['template'],
								'root_folder_id'=>$site['files_folder_id'],
								'default_template'=>$item['default_template'],
								'path'=> $path.urlencode($item['name'])
				);

				$subitems = $cms->get_items($item['id']);

				if(!count($subitems)) {
					$folderNode['expanded']=true;
					$folderNode['children']=array();
				}

				$response[] = $folderNode;
			} else {
				$children = create_tree($item['id'],$site,$path,$filter_enabled);
				foreach($children as $child) {
					$response[] = $child;
				}
			}
		}
	}

	return $response;
}

function get_folder_nodes($folder_id, $site, $path='') {
	global $GO_SECURITY;
	$cms = new cms();
	$filter_enabled=$cms->filter_enabled($GO_SECURITY->user_id,$site['id']);
	return create_tree($folder_id,$site,$path,$filter_enabled);
}

$task=isset($_REQUEST['task']) ? ($_REQUEST['task']) : '';
try {
	switch($task) {


		case 'tree':

			$cms2 = new cms();

			if(isset($_REQUEST['node']) && strpos($_REQUEST['node'],'_')) {
				$node = explode('_',$_REQUEST['node']);
				$node_type=$node[0];
				$folder_id=$node[1];
			}else {
				$node_type='root';
				$folder_id=0;
			}

			if($node_type=='site') {
				$site = $cms->get_site($folder_id);
				$response = get_folder_nodes($site['root_folder_id'], $site);
			}else {
				$response=array();
				if($folder_id==0) {
					$cms->get_authorized_sites($GO_SECURITY->user_id);

					while($cms->next_record()) {
						$response[] = array(
										'text'=>$cms->f('name'),
										'id'=>'folder_'.$cms->f('root_folder_id'),
										'iconCls'=>'folder-account',
										'expanded'=>count($response)==0,
										'fstype'=>'folder',
										'site_id'=>$cms->f('id'),
										'folder_id'=>$cms->f('root_folder_id'),
										'template'=>$cms->f('template'),
										'files_folder_id'=>$cms->f('files_folder_id'),
										'root_folder_id'=>$cms->f('files_folder_id'),
										'path'=>'',
										'children'=>get_folder_nodes($cms->f('root_folder_id'), $cms->record),
										'draggable'=>false
						);
					}
				}else {

					$folder = $cms->get_folder($folder_id);
					$site = $cms->get_site($folder['site_id']);

					$path = $cms->build_path($folder_id, true, $site['root_folder_id']);

					$response = get_folder_nodes($folder_id, $site, $path);
				}
			}

			break;



		case 'site':

			if(!$GO_MODULES->modules['cms']['write_permission']) {
				throw new AccessDeniedException();
			}

			$site = $cms->get_site($_REQUEST['site_id']);

			require_once($GO_CONFIG->class_path.'base/users.class.inc.php');
			$GO_USERS = new GO_USERS();

			$site['user_name']=$GO_USERS->get_user_realname($site['user_id']);

			$response['data']=$site;

			$response['success']=true;
			break;



		case 'sites':


			if(isset($_POST['delete_keys'])) {
				try {
					$response['deleteSuccess']=true;
					$delete_sites = json_decode(($_POST['delete_keys']));
					foreach($delete_sites as $site_id) {
						$cms->delete_site($site_id);
					}
				}catch(Exception $e) {
					$response['deleteSuccess']=false;
					$response['deleteFeedback']=$e->getMessage();
				}
			}
			$sort = isset($_REQUEST['sort']) ? ($_REQUEST['sort']) : 'id';
			$dir = isset($_REQUEST['dir']) ? ($_REQUEST['dir']) : 'DESC';
			$start = isset($_REQUEST['start']) ? ($_REQUEST['start']) : '0';
			$limit = isset($_REQUEST['limit']) ? ($_REQUEST['limit']) : '0';
			$response['total'] = $cms->get_authorized_sites($GO_SECURITY->user_id, $sort, $dir, $start, $limit);
			$response['results']=array();

			require_once($GO_CONFIG->class_path.'base/users.class.inc.php');
			$GO_USERS = new GO_USERS();

			while($cms->next_record()) {
				$site = $cms->record;

				$site['user_name']=$GO_USERS->get_user_realname($site['user_id']);
				$response['results'][] = $site;
			}
			break;


		case 'folder':


			if(!empty($_REQUEST['folder_id'])) {
				$folder = $cms->get_folder(($_REQUEST['folder_id']));
				$site = $cms->get_site($folder['site_id']);

				$folder['mtime']=Date::get_timestamp($folder['mtime']);
				$folder['ctime']=Date::get_timestamp($folder['ctime']);

				$folder['option_values']=$cms->get_template_values($folder['option_values']);

				$response['data']=$folder;

				$response['data']['authentication']=$folder['acl']>0;


				$response['data']['write_permission']=$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']);
				if(!$response['data']['write_permission']) {
					throw new AccessDeniedException();
				}
			}else {
				//load an empty file to get the template options
				$folder=$cms->get_folder(($_REQUEST['parent_id']));
				$site = $cms->get_site($folder['site_id']);
				$response['data']['acl']=0;
				$response['data']['parent_id']=$_REQUEST['parent_id'];
				$response['data']['option_values']=$cms->get_template_values($folder['option_values']);
			}

			$response['data']['config']=$cms->get_template_config($site['template']);

			$response['success']=true;
			break;



		case 'folders':

			$site_id=$_POST['site_id'];
			$site = $cms->get_site($site_id);
			$response['write_permission']=$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']);
			if(!$response['write_permission']) {
				throw new AccessDeniedException();
			}

			if(isset($_POST['delete_keys'])) {
				try {
					$response['deleteSuccess']=true;
					$delete_folders = json_decode(($_POST['delete_keys']));
					foreach($delete_folders as $folder_id) {
						$cms->delete_folder($folder_id);
					}
				}catch(Exception $e) {
					$response['deleteSuccess']=false;
					$response['deleteFeedback']=$e->getMessage();
				}
			}
			$sort = isset($_REQUEST['sort']) ? ($_REQUEST['sort']) : 'id';
			$dir = isset($_REQUEST['dir']) ? ($_REQUEST['dir']) : 'DESC';
			$start = isset($_REQUEST['start']) ? ($_REQUEST['start']) : '0';
			$limit = isset($_REQUEST['limit']) ? ($_REQUEST['limit']) : '0';
			$response['total'] = $cms->get_folders($site_id, $sort, $dir, $start, $limit);
			$response['results']=array();
			while($cms->next_record()) {
				$folder = $cms->record;
				$folder['mtime']=Date::get_timestamp($folder['mtime']);
				$folder['ctime']=Date::get_timestamp($folder['ctime']);

				$response['results'][] = $folder;
			}
			break;


		case 'file':

			global $GO_LANGUAGE;
			require_once($GO_LANGUAGE->get_language_file('cms'));

			if(!empty($_REQUEST['file_id'])) {
				$file = $cms->get_file(($_REQUEST['file_id']));
				$folder = $cms->get_folder($file['folder_id']);
				$site = $cms->get_site($folder['site_id']);

				$file['mtime']=Date::get_timestamp($file['mtime']);
				$file['ctime']=Date::get_timestamp($file['ctime']);
				$file['show_until']=Date::get_timestamp($file['show_until'],false);
				$file['sort_date']=Date::get_timestamp($file['sort_time'],false);
				$file['enable_categories']=$site['enable_categories'];

				$response['data']=$file;
				$response['data']['root_folder_id']=$site['files_folder_id'];

				$response['data']['config']=$cms->get_template_config($site['template']);

				$response['data']['write_permission']=$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']);
				if(!$response['data']['write_permission']) {
					throw new AccessDeniedException();
				}
			}else {
				//load an empty file to get the template options
				$folder = $cms->get_folder($_REQUEST['folder_id']);
				$site = $cms->get_site($folder['site_id']);

				$response['data']['files_folder_id']=0;
				$response['data']['root_folder_id']=$site['files_folder_id'];
				$response['data']['type']=$folder['type'];
				$response['data']['config']=$cms->get_template_config($site['template']);
				if(!empty($folder['default_template'])) {
					for($i=0;$i<count($response['data']['config']['templates']);$i++) {
						if($response['data']['config']['templates'][$i][0]==$folder['default_template']) {
							$response['data']['content']=$response['data']['config']['templates'][$i][1];
							break;
						}
					}
				}
				$response['data']['option_values']=$cms->get_template_values($folder['option_values']);
			}

			if (empty($response['data']['config'])) {
				require($GO_LANGUAGE->get_language_file('cms'));
				throw new Exception($lang['cms']['template_not_found']);
			}

			$response['success']=true;
			break;

		case 'files':
			$site_id=$_POST['site_id'];
			$site = $cms->get_site($site_id);
			$response['write_permission']=$GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write']);
			if(!$response['write_permission']) {
				throw new AccessDeniedException();
			}

			if(isset($_POST['delete_keys'])) {
				try {
					$response['deleteSuccess']=true;
					$delete_files = json_decode(($_POST['delete_keys']));
					foreach($delete_files as $file_id) {
						$cms->delete_file($file_id);
					}
				}catch(Exception $e) {
					$response['deleteSuccess']=false;
					$response['deleteFeedback']=$e->getMessage();
				}
			}
			$sort = isset($_REQUEST['sort']) ? ($_REQUEST['sort']) : 'id';
			$dir = isset($_REQUEST['dir']) ? ($_REQUEST['dir']) : 'DESC';
			$start = isset($_REQUEST['start']) ? ($_REQUEST['start']) : '0';
			$limit = isset($_REQUEST['limit']) ? ($_REQUEST['limit']) : '0';
			$response['total'] = $cms->get_files($site_id, $sort, $dir, $start, $limit);
			$response['results']=array();
			while($cms->next_record()) {
				$file = $cms->record;
				$file['mtime']=Date::get_timestamp($file['mtime']);
				$file['ctime']=Date::get_timestamp($file['ctime']);

				$response['results'][] = $file;
			}
			break;


		case 'templates':

			require_once($GO_CONFIG->class_path.'filesystem.class.inc');
			$fs = new filesystem();

			$response['results']=$fs->get_folders($GO_MODULES->modules['cms']['path'].'templates');

			break;


		case 'comment':
			$comment = $cms->get_comment(($_REQUEST['comment_id']));

			require_once($GO_CONFIG->class_path.'base/users.class.inc.php');
			$GO_USERS = new GO_USERS();


			$comment['user_name']=$GO_USERS->get_user_realname($comment['user_id']);
			$comment['ctime']=Date::get_timestamp($comment['ctime']);
			$response['data']=$comment;

			$response['success']=true;
			break;



		case 'comments':
			if(isset($_POST['delete_keys'])) {
				try {
					$response['deleteSuccess']=true;
					$delete_comments = json_decode(($_POST['delete_keys']));
					foreach($delete_comments as $comment_id) {
						$cms->delete_comment($comment_id);
					}
				}catch(Exception $e) {
					$response['deleteSuccess']=false;
					$response['deleteFeedback']=$e->getMessage();
				}
			}
			$sort = isset($_REQUEST['sort']) ? ($_REQUEST['sort']) : 'id';
			$dir = isset($_REQUEST['dir']) ? ($_REQUEST['dir']) : 'DESC';
			$start = isset($_REQUEST['start']) ? ($_REQUEST['start']) : '0';
			$limit = isset($_REQUEST['limit']) ? ($_REQUEST['limit']) : '0';
			$response['total'] = $cms->get_comments($sort, $dir, $start, $limit);
			$response['results']=array();

			require_once($GO_CONFIG->class_path.'base/users.class.inc.php');
			$GO_USERS = new GO_USERS();

			while($cms->next_record()) {
				$comment = $cms->record;

				$comment['user_name']=$GO_USERS->get_user_realname($comment['user_id']);
				$comment['ctime']=Date::get_timestamp($comment['ctime']);


				$response['results'][] = $comment;
			}
			break;

		case 'tree-edit':
			$cms = new cms();

			$user_id = ($_POST['user_id']);
			$site_id = ($_POST['site_id']);

			$site = $cms->get_site($site_id);
			$cms->get_folders($site['root_folder_id']);

			$response = array();
			while ($child_folder = $cms->next_record()) {
				$response[] = get_folder_tree($child_folder['id'],$user_id);
			}

			break;

		case 'writing_users':

			if($GO_MODULES->modules['cms']['write_permission']) {

			$writing_groups = json_decode($_POST['group_ids']);
			$writing_users = json_decode($_POST['user_ids']);

			require_once($GO_MODULES->modules['users']['class_path'].'users.class.inc.php');
			$users = new users();

			foreach($writing_groups as $group_id) {
				$users->get_users($group_id);
				while($user = $users->next_record()) {
					if (!contains($writing_users,$user['id']))
						$writing_users[] = $user['id'];
				}
			}

			$response['results'] = array();
			foreach($writing_users as $user_id) {
				$user = $users->get_user($user_id);
				$user['name'] = String::format_name($user);
				$response['results'][] = $user;
			}

			$response['success'] = true;

			} else {

				$response['feedback'] = $lang['cms']['no_admin_rights'];
				$response['success'] = false;

			}
			break;

		case 'is_admin':

			$response['is_admin'] = $GO_MODULES->modules['cms']['write_permission'];
			$response['success'] = true;

			break;

		case 'filter':
			$cms = new cms();

			$response['data']['filter'] = $cms->filter_enabled($_POST['user_id'],$_POST['site_id']);
			$response['success'] = true;

			break;

		case 'folder_files':
			$folder_id=$_POST['folder_id'];
			global $GO_LANGUAGE;
			require_once($GO_LANGUAGE->get_language_file('cms'));
			
			$response['total'] = $cms->get_files($folder_id);
			$response['results']=array();
			$response['results'][] = array('id'=>0,'name'=>$lang['cms']['none']);
			while($cms->next_record()) {
				$file = $cms->record;
				$file['mtime']=Date::get_timestamp($file['mtime']);
				$file['ctime']=Date::get_timestamp($file['ctime']);
				$response['results'][] = $file;
			}
			break;

		case 'file_categories':
			
			$file_id = $_POST['file_id'];
			$file = $cms->get_file($file_id);
			$folder = $cms->get_folder($file['folder_id']);

			$site_id = $folder['site_id'];
			$site = $cms->get_site($site_id);
		
			if(isset($_POST['delete_keys'])) {
				if($GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write'])<GO_SECURITY::DELETE_PERMISSION) {
					throw new AccessDeniedException();
				}
				
				try {
					$response['deleteSuccess']=true;
					$delete_categories = json_decode(($_POST['delete_keys']));
					foreach($delete_categories as $del_cat_id) {
						$cms->delete_category($del_cat_id);
					}
				}catch(Exception $e) {
					$response['deleteSuccess']=false;
					$response['deleteFeedback']=$e->getMessage();
				}
			}
			
			$site_categories = $cms->get_categories($site_id);
			$file_categories = $cms->get_categories_of_file($file_id);
			
			$response['total'] = count($site_categories);
			$response['success'] = true;
			$response['results'] = array();
			foreach ($site_categories as $site_category) {
				$record = $site_category;
				$record['used'] = in_array($site_category,$file_categories);
				$response['results'][] = $record;
			}
			
			break;
			
		case 'categories_tree':

			$file_id = intval($_POST['file_id']);
			
			$file = $cms->get_file($file_id);
			$folder = $cms->get_folder($file['folder_id']);
			$site_id = $folder['site_id'];
			
			if(!empty($_POST['delete_key'])) {
				$site = $cms->get_site($site_id);
				
				if($GO_SECURITY->has_permission($GO_SECURITY->user_id, $site['acl_write'])<GO_SECURITY::DELETE_PERMISSION) {
					throw new AccessDeniedException();
				}
				
				try {
					$response['deleteSuccess']=true;
					$cms->delete_category(intval($_POST['delete_key']),true);
				}catch(Exception $e) {
					$response['deleteSuccess']=false;
					$response['deleteFeedback']=$e->getMessage();
				}
			}
			
			$categories = $cms->get_categories($site_id,0);

			$response = array();
			foreach ($categories as $child_category) {
				$response[] = $cms->get_category_tree($child_category['id'],$site_id,$file_id);
			}
			
			$response = array(array(
					'id' => 0,
					'text' => 'Root',
					'checked' => false,
					'canHaveChildren' => true,
					'children' => $response,
					'expanded' => true
			));

			break;
			
		/* {TASKSWITCH} */
	}
} catch(Exception $e) {
	$response['feedback']=$e->getMessage();
	$response['success']=false;
}
echo json_encode($response);
