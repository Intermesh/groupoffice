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
 * @version $Id: links.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */

/**
 * Functions to create links between items in Group-Office
 * 
 * This class provides functions to create links between items in Group-Office such as
 * tasks, projects, notes, appointments, files etc.
 *
 * Link types are static ints to improve perfomance. The table below is a type 
 * reference:
 *
 * 1=cal_events
 * 2=ab_contacts
 * 3=ab_companies
 * 4=no_notes
 * 5=pm_projects
 * 6=files
 * 7=bs_orders
 * 8=users
 * 9=em_links
 * 10=timeregistration
 * 11=license
 * 12=tasks
 * 13=installation
 * 14=Project reports
 * 15=Custom database
 * 16=fs_docbundles
 * 17=folders
 * 18=calllog
 * 19=timeregistration
 * 20=Tickets
 * 21=Calendars
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: links.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic
 * 
 * @uses db
 */

class GO_LINKS extends db
{	
	function add_folder($folder)
	{
		$folder['id']=$this->nextid('go_link_folders');
		$this->insert_row('go_link_folders', $folder);
		return $folder['id'];
	}
	
	function update_folder($folder)
	{		
		$this->update_row('go_link_folders', 'id', $folder);		
	}

	function get_folder_by_name($name, $link_id, $link_type, $parent_id=0){
		$sql = "SELECT * FROM go_link_folders";
		if($parent_id)
		{
			$sql .= " WHERE parent_id=".intval($parent_id);
		}else
		{
			$sql .= " WHERE link_id=".intval($link_id)." AND link_type=".intval($link_type)." AND parent_id=0";
		}

		$sql .= " AND name='".$this->escape($name)."'";
		$this->query($sql);
		return $this->next_record();
	}
	
	function get_folder($folder_id)
	{
		$sql = "SELECT * FROM go_link_folders WHERE id=".intval($folder_id);
		$this->query($sql);
		if($this->next_record())
		{
			return $this->record;
		}
		return false;
	}
	
	function get_folders($link_id, $link_type, $parent_id=0)
	{
		$sql = "SELECT * FROM go_link_folders";
		if($parent_id)
		{
			$sql .= " WHERE parent_id=".intval($parent_id);
		}else
		{
			$sql .= " WHERE link_id=".intval($link_id)." AND link_type=".$this->escape($link_type)." AND parent_id=0";
		}
		
		$this->query($sql);
		
		return $this->num_rows();
	}
	
	function delete_folder($folder_id)
	{		
		$folder = $this->get_folder($folder_id);
		
		$this->get_folders(0,0,$folder_id);
		while($this->next_record())
		{
			$links = new GO_LINKS();
			$links->delete_folder($this->f('id'));	
		}
		
		$sql = "DELETE FROM go_links_".intval($folder['link_type'])." WHERE folder_id=".intval($folder_id);
		$this->query($sql);
		
		$sql = "DELETE FROM go_link_folders WHERE id=".intval($folder_id);
		$this->query($sql);		
	}
	
	function get_link_types()
	{
		
	}
	
	function is_sub_folder($sub_id, $parent_id)
	{
		if($sub_id==0)
		{
			return false;
		}
		$folder = $this->get_folder($sub_id);
		if($folder['parent_id']==$parent_id)
		{
			return true;
		}else
		{
			return $this->is_sub_folder($folder['parent_id'], $parent_id);
		}
	}
	
	function update_link($type, $link)
	{
			$this->update_row('go_links_'.$type, array('id', 'link_id','link_type'), $link);
	}
	
	function add_link($id1, $type1, $id2, $type2, $folder_id1=0, $folder_id2=0, $description1='', $description2='')
	{
		if(empty($id1) || empty($id2) || empty($type1) || empty($type2)){
			return false;			
		}
		
		
		if(!$this->link_exists($id1, $type1, $id2, $type2))
		{
			$link['id'] = $id1;
			$link['folder_id'] = $folder_id1;
			$link['model_type_id'] = $type2;
			$link['model_id'] = $id2;
			$link['description'] = $description1;
			$link['ctime']=time();

			//go_debug($link);
			
			$model = get_model_by_type_id($type1);
			$table1 = $model->tableName();
	
			$this->insert_row('go_links_'.$table1,$link);
			//if($update_link_count)
				//$this->update_link_count($id1, $type1);
		}
		
		if(!$this->link_exists($id2, $type2, $id1, $type1))
		{
			$link['id'] = $id2;
			$link['folder_id'] = $folder_id2;
			$link['model_type_id'] = $type1;
			$link['model_id'] = $id1;
			$link['description'] = $description2;
			$link['ctime']=time();
			
			$model = get_model_by_type_id($type2);
			$table2 = $model->tableName();
				
			$this->insert_row('go_links_'.$table2,$link);

			//if($update_link_count)
				//$this->update_link_count($id2, $type2);
		}		
	}

	/*function update_link_count($link_id, $link_type){
		$link['id']=$link_id;
		$link['link_type']=$link_type;
		$link['link_count']=$this->count_links($link_id, $link_type);

		$this->update_row('go_search_cache', array('id', 'link_type'), $link);
	}*/
	
	function link_exists($link_id1, $type1, $link_id2, $type2)
	{
		$model = get_model_by_type_id($type1);
		$table1 = $model->tableName();
			
		$sql = "SELECT * FROM go_links_".$table1." WHERE ".
			"`id`=".intval($link_id1)." AND model_type_id=".intval($type2)." AND `model_id`=".intval($link_id2);
		$this->query($sql);
		return $this->next_record();
	}
	
	function delete_link($link_id1, $type1, $link_id2=0, $type2=0)
	{		
			$model = get_model_by_type_id($type1);
			
			$table1 = $model->tableName();
			
			$model = get_model_by_type_id($type2);
			
			$table2 = $model->tableName();
		//if($link_id1>0)
		//{
			if($link_id2>0)
			{
				$sql = "DELETE FROM go_links_".$table1." WHERE id=".intval($link_id1)." AND model_type_id=".$this->escape($type2)." AND id=".intval($link_id2);
				$this->query($sql);
				
				$sql = "DELETE FROM go_links_".$table2." WHERE id=".intval($link_id2)." AND model_type_id=".$this->escape($type1)." AND id=".intval($link_id1);
				$this->query($sql);

				/*if($update_link_count){
					$this->update_link_count($link_id1, $type1);
					$this->update_link_count($link_id2, $type2);
				}*/
			}else
			{
				$sql = "SELECT DISTINCT link_type FROM go_links_".$table1." WHERE model_id=".intval($link_id1);
				//go_debug($sql);
				$this->query($sql);
				
				$db = new db();
	
				$l = new GO_LINKS();
				
				while($this->next_record())
				{

					/*$sql = "SELECT id FROM go_links_".intval($this->f('link_type'))." WHERE link_id=".intval($link_id1);
					
					$touched_items=array();
					$db->query($sql);
					while($r=$db->next_record()){
						$touched_items[]=$r['id'];
					}*/
					
					$model = get_model_by_type_id($this->f('link_type'));
					
					$db->query("DELETE FROM go_links_".$model->tableName()." WHERE id=".intval($link_id1));
					/*foreach($touched_items as $i){
						$l->update_link_count($i, intval($this->f('link_type')));
					}*/
					
				}
				$sql = "DELETE FROM go_links_".$table1." WHERE model_id=".intval($link_id1);				
				$this->query($sql);

				//$this->update_link_count($link_id1, $type1);
			}
		//}
		return true;
	}
	
	function has_links($link_id, $type)
	{
		return $this->count_links($link_id, $type);
	}

	function count_links($link_id, $type)
	{
		$model = get_model_by_type_id($type);
		if(!$model)
			return false;
		return $model->countLinks($link_id);

//		
//		if($link_id > 0)
//		{
//			$sql = "SELECT count(*) AS count FROM go_links_".intval($type)." WHERE model_id=".intval($link_id);
//			$this->query($sql);
//			$r =$this->next_record();
//			return $r['count'];
//		}
//		return false;
	}
	
	/*
	 * 
	 * todo
	 */
	function copy_links($src_link_id, $src_link_type, $dst_link_id, $dst_link_type)
	{
		global $GO_CONFIG;
		
		require_once($GLOBALS['GO_CONFIG']->class_path . '/base/search.class.inc.php');
		$search = new search();
		
		$search->global_search(1, "", 0, 0, 'id','ASC', array(), $src_link_id, $src_link_type,0);

		go_debug("copy_links($src_link_id, $src_link_type, $dst_link_id, $dst_link_type)");

		while($link = $search->next_record())
		{
			$this->add_link($dst_link_id, $src_link_type, $link['id'], $link['link_type']);
		}
	}
	
	
	
	/**
	 * Add a LinkDescription
	 *
	 * @param Array $link_description Associative array of record fields
	 *
	 * @access public
	 * @return int New record ID created
	 */
	function add_link_description($link_description)
	{
		$link_description['id']=$this->nextid('li_link_descriptions');
		if($this->insert_row('go_link_descriptions', $link_description))
		{
			return $link_description['id'];
		}
		return false;
	}
	/**
	 * Update a LinkDescription
	 *
	 * @param Array $link_description Associative array of record fields
	 *
	 * @access public
	 * @return bool True on success
	 */
	function update_link_description($link_description)
	{
		$r = $this->update_row('go_link_descriptions', 'id', $link_description);
		return $r;
	}
	/**
	 * Delete a LinkDescription
	 *
	 * @param Int $link_description_id ID of the link_description
	 *
	 * @access public
	 * @return bool True on success
	 */
	function delete_link_description($link_description_id)
	{
		return $this->query("DELETE FROM go_link_descriptions WHERE id=?", 'i', $link_description_id);
	}
	/**
	 * Gets a LinkDescription record
	 *
	 * @param Int $link_description_id ID of the link_description
	 *
	 * @access public
	 * @return Array Record properties
	 */
	function get_link_description($link_description_id)
	{
		$this->query("SELECT * FROM go_link_descriptions WHERE id=?", 'i', $link_description_id);
		return $this->next_record();		
	}

	/**
	 * Gets all LinkDescriptions
	 *
	 * @param Int $start First record of the total record set to return
	 * @param Int $offset Number of records to return
	 * @param String $sortfield The field to sort on
	 * @param String $sortorder The sort order
	 *
	 * @access public
	 * @return Int Number of records found
	 */
	function get_link_descriptions($query, $sortfield='id', $sortorder='ASC', $start=0, $offset=0)
	{
		$sql = "SELECT ";		
		if($offset>0)
		{
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}		
		$sql .= "* FROM go_link_descriptions ";
		$types='';
		$params=array();
		if(!empty($query))
 		{
 			$sql .= " WHERE description LIKE ?";
 			$types .= 's';
 			$params[]=$query;
 		} 		
		$sql .= " ORDER BY ".$this->escape($sortfield.' '.$sortorder);	
		if($offset>0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);
		}
		$this->query($sql, $types, $params);
		return $offset>0 ? $this->found_rows() : $this->num_rows();
	}
}
