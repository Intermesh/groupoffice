<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: custom_export_query.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * Example override for a custom export
 *
 * We'll base this export on the CSV export so include it.
 */
require_once($GLOBALS['GO_CONFIG']->class_path.'export/csv_export_query.class.inc.php');

class custom_export_query extends csv_export_query
{
	var $query='orders';
	var $name='My custom export';

	/**
	 * Optionally hardcode the list and text separator in the constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->list_separator="\t";
		$this->text_separator='';
	}

	/**
	 * Format the raw database record.
	 */

	function format_record(&$record){
		$record['veld6']='T';
		$record['veld8']='False';
		$record['btime']=date('d/m/Y', $record['btime']);
	}

	/**
	 * Initialize the columns.
	 * Some example custom fields are used here
	 */

	function init_columns(){
		$customfield = $this->cf->find_first_field_by_name(7, 'Some custom field');
		
		$this->columns=array('order_id','btime','customer_name', 'col_'.$customfield['id'],'veld6', 'total', 'veld8');
		$this->headers=array();
	}
}
