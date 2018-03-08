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
 * @version $Id: reminder.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */


/**
 * class to set reminders in Group-Office.
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: reminder.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @package go.basic
 * @since Group-Office 2.17
 * 
 * @uses db
 */

class reminder extends db
{
	/**
	* Add a ticket
	*
	* @param Array $ticket Associative array of record fields
	*
	* @access public
	* @return int New record ID created
	*/
	   
	function add_reminder($reminder)
	{
		$reminder['id']=$this->nextid('go_reminders');

		if(!empty($reminder['user_id'])){
			$this->add_user_to_reminder($reminder['user_id'], $reminder['id'], $reminder['time']);			
		}
		unset($reminder['user_id']);

		if(!isset($reminder['snooze_time']))
			$reminder['snooze_time']=7200;

		if($this->insert_row('go_reminders', $reminder))
		{
			return $reminder['id'];
		}
		return false;
	}

	function user_in_reminder($user_id, $reminder_id){
		$sql = "SELECT * FROM go_reminders_users WHERE user_id=? AND reminder_id=?";
		$this->query($sql, 'ii', array($user_id, $reminder_id));
		return $this->next_record();
	}

	function add_user_to_reminder($user_id, $reminder_id, $time){
		
		$r['user_id']=$user_id;
		$r['reminder_id']=$reminder_id;
		$r['time']=$time;

		return $this->replace_row('go_reminders_users', $r);
	}

	function reminder_mail_sent($user_id, $reminder_id){
		$sql = "UPDATE go_reminders_users SET mail_sent='1' WHERE user_id=? AND reminder_id=?";
		return $this->query($sql, 'ii', array($user_id, $reminder_id));
	}

	function remove_user_from_reminder($user_id, $reminder_id, $delete_reminder_if_last_user=true){
		$sql = "DELETE FROM go_reminders_users WHERE user_id=? AND reminder_id=?";
		$this->query($sql, 'ii', array($user_id, $reminder_id));

		if($delete_reminder_if_last_user && !$this->get_reminder_users($reminder_id))
			$this->delete_reminder($reminder_id);

		return true;

	}

	function remove_reminder_users($reminder_id){
		$sql = "DELETE FROM go_reminders_users WHERE reminder_id=?";
		return $this->query($sql, 'i', array($reminder_id));
	}

	function get_reminder_users($reminder_id, $with_user_info=false, $start=0,$offset=0){

		$sql = "SELECT ";

		if($offset>0)
		{
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}

		if($with_user_info){

			if(!isset($_SESSION['GO_SESSION']['sort_name']) ||  $_SESSION['GO_SESSION']['sort_name'] == 'first_name')
			{
				$sort = 'first_name ASC, last_name ASC';
			}else
			{
				$sort = 'last_name ASC, first_name ASC';
			}

			$sql .= "r.user_id, u.id, u.first_name, u.middle_name,u.last_name,u.email FROM ".
				"go_reminders_users r INNER JOIN go_users u ON u.id=r.user_id ".
				"WHERE r.reminder_id=? ORDER BY $sort";			
		}else
		{
			$sql .= "* FROM go_reminders_users WHERE reminder_id=?";
		}
		
		if($offset>0)
		{
			$sql .= " LIMIT ".intval($start).",".intval($offset);
		}
		$this->query($sql,'i', $reminder_id);
		return $offset>0 ? $this->found_rows() : $this->num_rows();
	}
	
	/**
	* Update a reminder
	*
	* @param Array $reminder Associative array of record fields
	*
	* @access public
	* @return bool True on success
	*/
	
	function update_reminder($reminder, $reset_mail_send=true)
	{
		if($reset_mail_send){
			$sql = "UPDATE go_reminders_users SET mail_sent=0";

			if(isset($reminder['time']))
				$sql.=',time='.$this->escape($reminder['time']);

			$sql .= " WHERE reminder_id=?";
			$this->query($sql, 'i', $reminder['id']);
		}

		if(!empty($reminder['user_id'])){
			$this->add_user_to_reminder($reminder['user_id'], $reminder['id'], $reminder['time']);
		}
		unset($reminder['user_id']);
			
		return $this->update_row('go_reminders', 'id', $reminder);
	}
	
	
	/**
	* Delete a reminder
	*
	* @param Int $reminder_id ID of the reminder
	*
	* @access public
	* @return bool True on success
	*/
	
	function delete_reminder($reminder_id)
	{
		$this->query("DELETE FROM go_reminders_users WHERE reminder_id=".$this->escape($reminder_id));

		return $this->query("DELETE FROM go_reminders WHERE id=".intval($reminder_id));
	}
	
	/**
	* Delete all reminders for a user ID
	*
	* @param Int $user_id ID of the user
	*
	* @access public
	* @return bool True on success
	*/
	
	/*function delete_reminders($user_id)
	{
		return $this->query("DELETE FROM go_reminders WHERE user_id=".intval($user_id));
	}*/
	
/**
	* Gets a reminder record by a link ID
	*
	* @param Int $model_id ID of the reminder
	*
	* @access public
	* @return Array Record properties
	*/
	
	function delete_reminders_by_model_id($model_id, $model_type_id)
	{
		$this->get_reminders_by_model_id($model_id, $model_type_id);

		$r = new reminder();
		while($reminder=$this->next_record()){
			$r->delete_reminder($reminder['id']);
		}
	}
	
	/**
	* Gets a reminder record by a link ID
	*
	* @param Int $model_id ID of the reminder
	*
	* @access public
	* @return Array Record properties
	*/
	
	function get_reminder_by_model_id($user_id, $model_id, $model_type_id)
	{
		$this->query("SELECT r.*,u.time FROM go_reminders r INNER JOIN go_reminders_users u ON u.reminder_id=r.id WHERE u.user_id=".intval($user_id)." AND model_id=".intval($model_id)." AND model_type_id=".intval($model_type_id));
		return $this->next_record();
	}
	
 /**
	* Get a reminders record by a link ID
	*
	* @param Int $model_id ID of the reminder
	*
	* @access public
	* @return Array Record properties
	*/
	
	function get_reminders_by_model_id($model_id, $model_type_id)
	{
		$this->query("SELECT * FROM go_reminders WHERE model_id=".intval($model_id)." AND model_type_id=".intval($model_type_id));
		return $this->num_rows();
	}
	

	
	
	/**
	* Gets a reminder record
	*
	* @param Int $reminder_id ID of the reminder
	*
	* @access public
	* @return Array Record properties
	*/
	
	function get_reminder($reminder_id)
	{
		$this->query("SELECT * FROM go_reminders WHERE id=".intval($reminder_id));
		return $this->next_record();
	}
	
	/**
	* Gets a reminder record by the name field
	*
	* @param String $name Name of the reminder
	*
	* @access public
	* @return Array Record properties
	*/
	
	function get_reminder_by_name($name)
	{
		$this->query("SELECT * FROM go_reminders WHERE reminder_name='".$this->escape($name)."'");
		return $this->next_record();
	}
	
	
	/**
	* Gets all reminders
	*
	* @param Int $start First record of the total record set to return
	* @param Int $offset Number of records to return
	* @param String $sortfield The field to sort on
	* @param String $sortorder The sort order
	*
	* @access public
	* @return Int Number of records found
	*/
	function get_reminders($user_id, $not_mailed=false)
	{
	 	$sql = "SELECT DISTINCT r.id, r.time, r.vtime, r.name, r.model_id,r.model_type_id,r.snooze_time, r.text FROM go_reminders r ".
		"LEFT JOIN go_reminders_users u ON u.reminder_id=r.id ".
		"WHERE u.user_id=? ".
		"AND u.time<?";

		if($not_mailed)
		{
			$sql .= ' AND u.mail_sent = 0';
		}
		$types='ii';
		$params=array(
			$user_id,
			time()
		);
		
		$this->query($sql, $types,$params);

		return $this->num_rows();		
	}

	/**
	 * Gets all Reminders
	 *
	 * @param Int $start First record of the total record set to return
	 * @param Int $offset Number of records to return
	 * @param String $sortfield The field to sort on
	 * @param String $sortorder The sort order
	 *
	 * @access public
	 * @return Int Number of records found
	 */
	function get_manual_reminders($query='', $sortfield='id', $sortorder='ASC', $start=0, $offset=0)
	{
		$sql = "SELECT ";
		if($offset>0)
		{
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}
		$sql .= "* FROM go_reminders ";

		$types='';
		$params=array();


		$sql .= "WHERE manual=1";
		

		
		if(!empty($query))
 		{
 			$sql .= " AND name LIKE ?";
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