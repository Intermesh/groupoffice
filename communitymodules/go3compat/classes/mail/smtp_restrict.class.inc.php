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
 * @version $Id: smtp_restrict.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.mail
 */

/**
 * This class restricts the number of outgoing messages to a SMTP host
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: smtp_restrict.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @license AGPL Affero General Public License
 * @package go.mail
 * @uses db
 * @since Group-Office 3.0
 */
class smtp_restrict extends db{
	
	var $hosts; 
	
	function __construct()
	{
		parent::__construct();
		
		$this->hosts = $this->get_hosts();
	}
	
	function get_hosts()
	{
		global $GO_CONFIG;
		
		$hosts = array();
		if(!empty($GLOBALS['GO_CONFIG']->restrict_smtp_hosts))
		{
			$expl = explode(',', $GLOBALS['GO_CONFIG']->restrict_smtp_hosts);
			
			foreach($expl as $restriction)
			{
				
				$arr = explode(':', $restriction);
				
				$hosts[$arr[0]]=$arr[1];
			}
		}
		return $hosts;
	}
	
	function is_allowed($host)
	{
		$host = gethostbyname($host);
		
		//go_debug(var_export($this->hosts, true));
		//go_debug($host);
		
		
		if(!isset($this->hosts[$host]))
		{
			return true;
		}else
		{
			$counter = $this->get_counter($host);			
			if(!$counter)
			{
				$counter['host']=$host;
				$counter['date']=date('Y-m-d');
				$counter['count']=1;
				$this->add_counter($counter);
			}else
			{
				if($counter['date']!=date('Y-m-d'))
				{
					$counter['date']=date('Y-m-d');
					$counter['count']=1;	
				}else
				{
					$counter['count']++;					
				}
				$this->update_counter($counter);
			}
			return $counter['count']<=$this->hosts[$host];
		}
	}
	
	/**
	 * Add a Counter
	 *
	 * @param Array $counter Associative array of record fields
	 *
	 * @access public
	 * @return int New record ID created
	 */

	function add_counter($counter)
	{		
		return $this->insert_row('go_mail_counter', $counter);
	}

	/**
	 * Update a Counter
	 *
	 * @param Array $counter Associative array of record fields
	 *
	 * @access public
	 * @return bool True on success
	 */

	function update_counter($counter)
	{		
		return $this->update_row('go_mail_counter', 'host', $counter);
	}


	/**
	 * Delete a Counter
	 *
	 * @param Int $counter_id ID of the counter
	 *
	 * @access public
	 * @return bool True on success
	 */

	function delete_counter($host)
	{		
		return $this->query("DELETE FROM go_mail_counter WHERE host='$host'");
	}


	/**
	 * Gets a Counter record
	 *
	 * @param Int $counter_id ID of the counter
	 *
	 * @access public
	 * @return Array Record properties
	 */

	function get_counter($host)
	{
		$this->query("SELECT * FROM go_mail_counter WHERE host='$host'");
		if($this->next_record())
		{
			return $this->record;
		}else
		{
			return false;
		}
	}
	
	
	
}
?>