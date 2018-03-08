<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: search.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */


/**
 * This class is used to search through all modules that support the __on_search 
 * function.
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: search.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @package go.basic
 * @since Group-Office 2.17
 * 
 * @uses db
 */

class search extends db {

	/**
	 * This function will call the __on_search function in all main module 
	 * classes
	 *
	 * @param boolean $verbose If you want to output some debuggin informatin
	 
	public function update_search_cache($verbose=false)
	{
		global $GO_MODULES;
		
		$line_break=php_sapi_name() != 'cli' ? '<br />' : "\n";
		
		foreach($GLOBALS['GO_MODULES']->modules as $module)
		{			
			$file = $module['class_path'].$module['id'].'.class.inc';

			if(!file_exists($file))
			{
				$file = $module['class_path'].$module['id'].'.class.inc.php';
			}
			if(file_exists($file))
			{
				require_once($file);
				if(class_exists($module['id']))
				{						
					$class = new $module['id'];

					if(method_exists($class, '__on_build_search_index'))
					{
						if($verbose)
						{
							echo 'Caching items from '.$module['id'].$line_break;
							flush();
						}
						$class->__on_build_search_index();
						$this->update_last_sync_time($module['id'], time());
					}
				}
			}
		}
	}*/

	/**
	 * Search the search cache table for any item 
	 *
	 * @param int $user_id The user ID for authentication
	 * @param String $query The search query
	 * @param int $start
	 * @param int $offset
	 * @param StringHelper $sort_index Sort the result on this field
	 * @param StringHelper $sort_order Order it DESC / ASC 
	 * @param array $selected_types An array of model_type_ids that should only be searched on.
	 * @param int $model_id Search only in items linked to this id and type
	 * @param int $model_type_id Search only in items linked to this id and type
	 * @param int $link_folder_id Show results from this link folder
	 * @param bool $omit_model_type_ids select all but the selected array of link types
	 * @return int The total records found
	 */
	function global_search($user_id, $query, $start, $offset, $sort_index='name', $sort_order='ASC', $selected_types=array(), $model_id=0, $model_type_id=0, $link_folder_id=0, $conditions=array(), $omit_model_type_ids=false)
	{
		
		
		
		$sql = "SELECT sc.acl_id, sc.user_id,sc.model_id AS id, sc.module, sc.name, sc.description,sc.model_type_id AS link_type, sc.type, sc.mtime";
		if($model_id>0)
		{
			$sql .= ",l.description AS link_description";
		}
		$sql .= " FROM go_search_cache sc ".
			"INNER JOIN go_acl a ON (sc.acl_id = a.acl_id AND (a.user_id=".intval($user_id)." or a.group_id IN (".implode(',',$GLOBALS['GO_SECURITY']->get_user_group_ids($user_id))."))) ";
				
		if($model_id>0)
		{
			$model = get_model_by_type_id($model_type_id);
			
			$sql .= "INNER JOIN go_links_{$model->tableName()} l ON l.model_id=sc.model_id AND l.model_type_id=sc.model_type_id ";		
		}
		 
		/*$sql .=	"WHERE EXISTS (".
				"SELECT acl_id FROM go_acl a ".
				"LEFT JOIN go_users_groups ug ON ug.group_id=a.group_id ".
				"WHERE (a.user_id=".intval($user_id)." OR ug.user_id=".intval($user_id).") AND ".
				"(a.acl_id=sc.acl_id)) ";*/		
		$where = false;
		if($link_folder_id>0)
		{
			$where = true;
			$sql .= "WHERE l.folder_id=".intval($link_folder_id)." ";
		}elseif($model_id>0)
		{
			$where = true;
			$sql .= "WHERE l.id=".intval($model_id)." ";
			
			if($link_folder_id>-1)
				$sql .= " AND l.folder_id=0 "; 
		}

		if(!empty($query))
		{
			

			$keywords = explode(' ', $query);

			$sql .= $where ? ' AND ' : ' WHERE ';
			$where = true;

			if(count($keywords)>1)
			{
				foreach($keywords as $keyword)
				{
					$sql_keywords[] = "keywords LIKE '%".$this->escape($keyword)."%'";
				}

				$sql .= '('.implode(' AND ', $sql_keywords).') ';
			}else {
				$sql .= "keywords LIKE '%".$this->escape($query)."%' ";
			}
		}
		
		if(count($selected_types))
		{
			$sql .= $where ? ' AND ' : ' WHERE ';
			$where = true;

			$sql .= "sc.model_type_id ";

			if($omit_model_type_ids)
				$sql .= "NOT ";

			$sql .= "IN (".implode(',', $selected_types).") ";
		}
		
		foreach($conditions as $condition)
		{
			$sql .= $where ? ' AND ' : ' WHERE ';
			$where = true;
			$sql .= $condition." ";
		}

		$sql .= " GROUP BY sc.model_id, sc.model_type_id";	

		if(!empty($sort_index))
			$sql .= " ORDER BY $sort_index $sort_order";

		//go_debug($sql);
		
		if($offset>0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);

			if($start==0)
				$sql = substr_replace($sql, 'SELECT SQL_CALC_FOUND_ROWS',0,6);

			$this->query($sql);

			if($start==0)
				$count = $_SESSION['GO_SESSION']['global_search_count']=$this->found_rows();
			else
				$count = $_SESSION['GO_SESSION']['global_search_count'];

			//$count=0;
		}else
		{
			$this->query($sql);
			$count = $this->num_rows();
		}
		return $count;
	}

	
	/*function global_search_oud($user_id, $query, $start, $offset, $sort_index='name', $sort_order='ASC', $selected_types=array(), $model_id=0, $model_type_id=0)
	{
		$this->update_search_cache();
		$sql = "SELECT DISTINCT sc.* FROM go_search_cache sc ";
			
		//WIth an offset the joins work faster then the subselects
		if($offset>0)
		{
			$sql .= "INNER JOIN go_acl a ON (sc.acl_read=a.acl_id OR sc.acl_write=a.acl_id) ".
				"INNER JOIN go_users_groups ug ON (ug.group_id=a.group_id) ";
		}
				
		if($model_id>0)
		{			
			$sql .= "INNER JOIN go_links l ON ".
				"((l.model_id1=sc.id AND l.type1=sc.model_type_id AND l.model_id2=$model_id AND l.type2=$model_type_id) OR ".
				"(l.model_id2=sc.id AND l.type2=sc.model_type_id AND l.model_id1=$model_id AND l.type1=$model_type_id)) ";		
		}		
		
		//WIth an offset the joins work faster then the subselects
		if($offset>0)
		{
			$sql .= "WHERE (a.user_id=".intval($user_id)." OR ug.user_id=".intval($user_id).") ";
		}else
		{		
			$sql .=	"WHERE (sc.acl_read IN (SELECT acl_id FROM go_acl a INNER JOIN go_users_groups ug ON ug.group_id=a.group_id WHERE a.user_id=".intval($user_id)." OR ug.user_id=".intval($user_id).") OR ".
				"sc.acl_write IN (SELECT acl_id FROM go_acl a INNER JOIN go_users_groups ug ON ug.group_id=a.group_id WHERE a.user_id=".intval($user_id)." OR ug.user_id=".intval($user_id).")) ";
		}


		if(!empty($query))
		{
			$keywords = explode(' ', $query);


			if(count($keywords)>1)
			{
				foreach($keywords as $keyword)
				{
					$sql_keywords[] = "keywords LIKE '%$keyword%'";
				}


				$sql .= ' AND ('.implode(' AND ', $sql_keywords).') ';
			}else {
				$sql .= " AND keywords LIKE '%$query%' ";
			}
		}
		
		if(count($selected_types))
		{
			$sql .= " AND model_type_id IN (".implode(',', $selected_types).") ";
		}
		
		$sql .= " ORDER BY sc.type ASC, mtime ASC";
		
		
		
		
		//$this->query($sql);	
		
		//$count = $this->num_rows();		
		
		if($offset>0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);			
		  $sql = substr_replace($sql, 'SELECT SQL_CALC_FOUND_ROWS',0,6);
			
			$this->query($sql);
			
			//$this->query("SELECT FOUND_ROWS() as count;");
		//	$this->next_record();
			
		//	$count = $this->f('count');		
		$count=0;
			
			//$this->query($sql);			
		}else
		{
			$this->query($sql);
			$count = $this->num_rows();
		}
		
		

		return $count;
	}*/
	
	function get_latest_links_json($user_id, $model_id, $model_type_id)
	{
		/*$conditions = array(
			'l.ctime>'.Date::date_add(time(), -90)
		);*/

		//events and tasks are omitted because they are in separate tables
		return $this->get_links_json($user_id,'',0,15,'l.ctime', 'DESC',array(1,12), $model_id,$model_type_id,-1, array(),true);
	}
	
	/**
	 * Get JSON data to display links. See also the global_search function for parameters
	 *
	 * @param unknown_type $user_id
	 * @param unknown_type $query
	 * @param unknown_type $start
	 * @param unknown_type $limit
	 * @param unknown_type $sort
	 * @param unknown_type $dir
	 * @param unknown_type $model_type_ids
	 * @param unknown_type $model_id
	 * @param unknown_type $model_type_id
	 * @param unknown_type $folder_id
	 * @return unknown
	 */
	function get_links_json($user_id, $query, $start, $limit, $sort,$dir, $model_type_ids, $model_id, $model_type_id,$folder_id, $conditions=array(), $omit_model_type_ids=false){
		
		global $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
		$GO_LINKS = new GO_LINKS();
		
		$response['results']=array();
		$response['total']=0;
		if(empty($query) && empty($model_id)){
			return $response;
		}
		
		
		
		if($model_id>0)
		{
			//$_folder_id = $folder_id>-1 ? $folder_id : 0;
			$GO_LINKS->get_folders($model_id, $model_type_id, $folder_id);
			while($link=$GO_LINKS->next_record())
			{
				$response['results'][]=array(
					'id'=>$link['id'],
					'parent_model_id'=>$model_id, 
					'parent_model_type_id'=>$model_type_id,
					'model_type_id'=>'folder',
					'link_and_type'=>'folder:'.$link['id'],
					'name'=>htmlspecialchars($link['name'],ENT_QUOTES, 'UTF-8'),
					'type'=>'Folder',
					'description'=>'',
					'link_description'=>'',					
					'mtime'=>'-',
					'iconCls'=>'filetype-folder'		
					);
			}
		}
		
		
		
		$response['total']=$this->global_search($user_id, $query, $start, $limit, $sort,$dir, $model_type_ids, $model_id, $model_type_id,$folder_id, $conditions, $omit_model_type_ids);

		while($this->next_record())
		{
			$response['results'][]=array(
				'iconCls'=>'go-link-icon-'.$this->f('model_type_id'),
				'id'=>$this->f('id'),
				'link_count'=>$GO_LINKS->count_links($this->f('id'), $this->f('model_type_id')),
				'model_type_id'=>$this->f('model_type_id'),
				'link_and_type'=>$this->f('model_type_id').':'.$this->f('id'),
				'type_name'=>'('.$this->f('type').') '.strip_tags($this->f('name')),
				'name'=>$this->f('name'),
				'type'=>$this->f('type'),
				'description'=>$this->f('description'),
				'link_description'=>htmlspecialchars($this->f('link_description'), ENT_QUOTES, 'utf-8'),
			//'url'=>$search->f('url'),
				'mtime'=>Date::get_timestamp($this->f('mtime')),
				'module'=>$this->f('module')//,
			//'id'=>$search->f('id')
			);
		}
		

		
		return $response;
	}
	
	/**
	 * Clear the entire search cache table. It will be regenerated on the next
	 * search action.
	 */

	function reset()
	{
		$sql = "TRUNCATE TABLE go_search_cache";
		$this->query($sql);

		$sql = "TRUNCATE TABLE go_search_sync";
		$this->query($sql);
	}

	/**
	 * Get a particular search result from the cache table
	 *
	 * @param unknown_type $id
	 * @param unknown_type $type
	 * @return unknown
	 */
	function get_search_result($id, $type)
	{
		$sql = "SELECT * FROM go_search_cache WHERE model_id=".intval($id)." AND model_type_id=".intval($type);
		$this->query($sql);
		if($this->next_record())
		{
			return $this->record;
		}
		return false;
	}

	/**
	 * Get the last time a module synced with the cache table
	 *
	 * @param String $module the name of the module
	 * @return int The UNIX timestamp of the last sync operation
	 */
	function get_last_sync_time($module)
	{
		$sql = "SELECT last_sync_time FROM go_search_sync WHERE module='".$this->escape($module)."'";
		$this->query($sql);
		if($this->next_record())
		{
			return $this->f('last_sync_time');
		}else {
			$lst['module']=$module;
			$lst['last_sync_time']=0;
			$this->insert_row('go_search_sync',$lst);

			return 0;
		}
	}
	
	/**
	 * Delete a search result from the cache table
	 *
	 *
	 * @param unknown_type $id
	 * @param unknown_type $model_type_id
	 */
	
	function delete_search_result($id, $model_type_id)
	{
		global $GO_MODULES, $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
		$GO_LINKS = new GO_LINKS();

		$sr = $this->get_search_result($id, $model_type_id);
		if($sr)
		{
			$sql = "DELETE FROM go_search_cache WHERE model_id=".intval($id)." AND model_type_id=".$this->escape($model_type_id);
			$this->query($sql);
			
			$this->log($id, $model_type_id, 'Deleted '.strip_tags($sr['name']));
			$GO_LINKS->delete_link($id, $model_type_id);			
		}
		if(isset($GLOBALS['GO_MODULES']->modules['customfields'])){
			require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'].'customfields.class.inc.php');
			$cf = new customfields();
			$cf->delete_cf_row($model_type_id, $id);
		}
	}
	
	/**
	 * Add a search result to the cache table
	 *
	 * @param Array $result the fields of the search result
	 */

	function cache_search_result($result)
	{
		global $lang;

//		if(isset($result['keywords']) && strlen($result['keywords'])>255)
//		{
//			$result['keywords']=substr($result['keywords'],0,255);
//		}

		//$result['link_count']=$GO_LINKS->count_links($result['id'], $result['model_type_id']);
		if(isset($result['link_type']))
			$result['model_type_id']=$result['link_type'];
		
		$result['model_id']=$result['id'];
		unset($result['link_type'], $result['id']);

		$old_result = $this->get_search_result($result['model_id'], $result['model_type_id']);
		if($old_result)
 		{
 			$this->update_row('go_search_cache',array('model_id', 'model_type_id'), $result);
			$this->log($result['model_id'], $result['model_type_id'], 'Updated '.strip_tags($result['name']));
 		}else {		
 			$cache['ctime']=time();
 			$this->insert_row('go_search_cache',$result);
 			$this->log($result['model_id'], $result['model_type_id'], 'Added '.strip_tags($result['name']));

			//create default link folders
			global $GO_CONFIG;

			require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
			$GO_LINKS = new GO_LINKS();
			
			$default_folders = $GLOBALS['GO_CONFIG']->get_setting('default_link_folder_'.$result['model_type_id']);
			if($default_folders){

				$default_folder_array=array();

				$lines = explode("\n", $default_folders);
				foreach($lines as $line){
					$folders = explode('/', $line);

					$parent_id=0;
					for($i=0;$i<count($folders);$i++)
					{
						$folder = $GO_LINKS->get_folder_by_name($folders[$i], $result['id'], $result['model_type_id'], $parent_id);
						if(!$folder){
							$parent_id = $GO_LINKS->add_folder(array(
								'name'=>$folders[$i],
								'model_id'=>$result['id'],
								'model_type_id'=>$result['model_type_id'],
								'parent_id'=>$parent_id
							));
						}else
						{
							$parent_id=$folder['id'];
						}
					}
				}
				//go_debug($default_folder_array);
			}
 		}
	}
	
	function log($model_id, $model_type_id, $text)
	{
//		global $GO_MODULES;
//		
//		if(isset($GLOBALS['GO_MODULES']->modules['log']) && !defined('NOLOG'))
//		{
//			$log['model_id']=$model_id;
//			$log['model_type_id']=$model_type_id;
//			$log['time']=time();
//			$log['text']=$text;
//			$log['user_id']=$GLOBALS['GO_SECURITY']->user_id;
//			$log['id']=$this->nextid('go_log');
//			
//			$this->insert_row('go_log', $log);
//		}
	}
	

	
	/**
	 * Get a string of search keyword from an array of a database record
	 *
	 * @param array $record The record from the database
	 * @return String keywords
	 */
	function record_to_keywords($record)
	{
		$keywords=array();

		foreach($record as $field)
		{
			if(!empty($field) && !is_numeric($field) && !in_array($field,$keywords))
			{
				$keywords[]=$field;
			}
		}
		return implode(',',$keywords);
	}

	/**
	 * Update the last time a module synced with the search cache table
	 *
	 * @param StringHelper $module
	 */
	function update_last_sync_time( $module)
	{
		$lst['module']=$module;
		$lst['last_sync_time']=time();

		$this->update_row('go_search_sync','module',$lst);
	}
	
	/**
	 * Return all of the search types that are available in the cache table
	 *
	 * @return array of search types
	 */
	function get_search_types()
	{
		if(!isset($_SESSION['GO_SESSION']['search_types']))
		{
			$sql = "SELECT DISTINCT model_type_id, type FROM go_search_cache";
			$this->query($sql);
			while($this->next_record())
			{
				$type['type']=$this->f('type');
				$type['model_type_id']=$this->f('model_type_id');

				$_SESSION['GO_SESSION']['search_types'][]=$type;
			}
		}
		return 	$_SESSION['GO_SESSION']['search_types'];
	}
}