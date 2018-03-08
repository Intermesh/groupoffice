<?php
/**
 * @copyright Copyright Intermesh
 * @version $Revision: 17810 $ $Date: 2006/04/12 15:09:08 $
 *
 * @author Merijn Schering <mschering@intermesh.nl>

 This program is protected by copyright law and the Group-Office Professional license.

 You should have received a copy of the Group-Office Proffessional license
 along with Group-Office; if not, write to:

 Intermesh
 Reitscheweg 37
 5232BX Den Bosch
 The Netherlands

 info@intermesh.nl

 * @package Custom fields
 * @category Custom fields
 */

/**
 * Functions to create Custom fields for items in Group-Office
 *
 * This class provides functions to create Custom fields for items in Group-Office such as
 * tasks, projects, notes, appointments, files etc.
 *
 * Data types are static ints to improve perfomance. The table below is a type
 * reference:
 *
 * 1=cal_events
 * 2=ab_contacts
 * 3=ab_companies
 * 4=no_notes
 * 5=pmProjects
 * 6=folders & files
 * 7=bs_orders
 * 8=users
 * 9=
 * 18=calllog
 * 19=timeregistration
 * 20=Tickets
 * 21=Calendars
 * 1001=shipping
 *
 * @access protected
 * @uses db
 */

global $customfield_types;
	class default_customfield_type{
		var $fieldsql = "VARCHAR(255)";

		function __construct($config=false){

			if($config){
				foreach($config as $key=>$value){
					$this->$key=$value;
				}
			}
		}

		function format_for_form($field, &$record, $fields){

		}
		function format_for_display($field, &$record, $fields){
			$this->format_for_form($field, $record, $fields);

			$record[$field['dataname']]=isset($record[$field['dataname']]) ? String::text_to_html($record[$field['dataname']] ) : '';
		}
		function format_for_database($field, &$record, &$post_values){
			$record[$field['dataname']] =isset($post_values[$field['dataname']])?$post_values[$field['dataname']]:'';
		}
	}

	$customfield_types['text']=new default_customfield_type();
	$customfield_types['textarea']=new default_customfield_type(array(
		'fieldsql'=>'TEXT NULL'
	));


	class select_customfield_type extends default_customfield_type{
		function format_for_form($field, &$record, $fields){
			if(!empty($field['multiselect']))
				$record[$field['dataname'].'[]'] = $record[$field['dataname']];
			
		}
		function format_for_display($field, &$record, $fields){
			if(!empty($field['multiselect'])){
				$record[$field['dataname']] = str_replace('|', ', ', $record[$field['dataname']]);
				if($record[$field['dataname']]==', ')
					$record[$field['dataname']]='';
			}
		}
	}

	//needs to be text for multiselect
	$customfield_types['select']=new select_customfield_type(array(
		'fieldsql'=>'TEXT NULL'
	));


	class yesno_customfield_type extends default_customfield_type{
		function format_for_display($field, &$record, $fields){
			global $GO_LANGUAGE, $lang;
			require_once($GLOBALS['GO_LANGUAGE']->get_language_file('customfields'));

			switch ($record[$field['dataname']]) {
				case '0':
					$record[$field['dataname']] = $lang['customfields']['undefined'];
					break;
				case '-1':
					$record[$field['dataname']] = $lang['common']['no'];
					break;
				case '1':
					$record[$field['dataname']] = $lang['common']['yes'];
					break;
			}
		}
	}

	//needs to be text for multiselect
	$customfield_types['yesno']=new yesno_customfield_type(array(
		'fieldsql'=>"enum('-1','0','1') NOT NULL DEFAULT '0'"
	));


	/*
	 * A treeselect consists of one master and one or more slave comboboxes.
	 * The slave is loaded with data depending on the selection of it's parent.
	 * The last slave can be a multiselect combo (superboxselect).
	 */
	class treeselect_customfield_type extends default_customfield_type {
		function format_for_form($field, &$record, $fields){
			if(!empty($field['multiselect']))
				$record[$field['dataname'].'[]'] = $record[$field['dataname']];
		}
		function format_for_display($field, &$record, $fields) {
			//global $GO_MODULES;

			if(!empty($record[$field['dataname']])) {

				//multiselect is only valid for the last treeselect_slave
				if(!empty($field['multiselect'])){

					$value_arr=array();
					$id_value_arr = explode('|', $record[$field['dataname']]);
					foreach($id_value_arr as $value){
						$id_value = explode(':', $value);
						if(isset($value[1]))
							$value_arr[]=$id_value[1];
					}

					$record[$field['dataname']] = implode(', ', $value_arr);

				}else
				{
					$value = explode(':', $record[$field['dataname']]);
					if(isset($value[1]))
						$record[$field['dataname']]=$value[1];
				}
			}
		}
	}

	$customfield_types['treeselect']=new treeselect_customfield_type();
	$customfield_types['treeselect_slave']=new treeselect_customfield_type();
	

	class html_customfield_type extends default_customfield_type{
		function format_for_display($field, &$record, $fields){
			$this->format_for_form($field, $record, $fields);
		}
	}

	$customfield_types['html']=new html_customfield_type(array(
			'fieldsql'=>'TEXT NULL'
	));


	class checkbox_customfield_type extends default_customfield_type{
		function format_for_display($field, &$record, $fields){
			
			if ($record[$field['dataname']]=='1') {
				$record[$field['dataname']]=$GLOBALS['lang']['common']['yes'];
			}else {
				$record[$field['dataname']]=$GLOBALS['lang']['common']['no'];
			}
		}
		function format_for_database($field, &$record, &$post_values){
			$record[$field['dataname']] =!empty($post_values[$field['dataname']]) ? 1 : 0;
		}
	}

	$customfield_types["checkbox"]=new checkbox_customfield_type(array(
		'fieldsql'=>'BOOL'
	));

	class number_customfield_type extends default_customfield_type{
		function format_for_form($field, &$record, $fields){
			$record[$field['dataname']] = Number::format($record[$field['dataname']]);
		}
		function format_for_database($field, &$record, &$post_values){
			if(isset($post_values[$field['dataname']]))
				$record[$field['dataname']] = Number::to_phpnumber($post_values[$field['dataname']]);
		}
	}

	$customfield_types["number"]=new number_customfield_type(array(
		'fieldsql'=>'DOUBLE NULL'
	));


	class date_customfield_type extends default_customfield_type{
		function format_for_database($field, &$record, &$post_values){
			$value =isset($post_values[$field['dataname']])?$post_values[$field['dataname']]:'';
			$value = Date::to_db_date(trim($value));
			if(isset($post_values[$field['dataname'].'_hour'])){
				$value .= ' '.$post_values[$field['dataname'].'_hour'].':'.$post_values[$field['dataname'].'_min'];
			}
			$record[$field['dataname']]=$value;
		}
		function format_for_form($field, &$record, $fields){
			$record[$field['dataname']] = !empty($record[$field['dataname']]) &&  $record[$field['dataname']] != '0000-00-00' ? Date::format($record[$field['dataname']], false) : '';
		}
	}

	$customfield_types["date"]=new date_customfield_type(array(
		'fieldsql'=>'DATE NULL'
	));



	class datetime_customfield_type extends date_customfield_type{
		function format_for_form($field, &$record, $fields){

			if(!empty($record[$field['dataname']]) &&  $record[$field['dataname']] != '0000-00-00 00:00:00'){
				$unixtime = strtotime($record[$field['dataname']]);

				$record[$field['dataname']] = Date::get_timestamp($unixtime, false);
				$record[$field['dataname'].'_hour'] = date('G', $unixtime);
				$record[$field['dataname'].'_min'] = date('i', $unixtime);
			}else
			{
				$record[$field['dataname']] = '';
				$record[$field['dataname'].'_hour'] = '';
				$record[$field['dataname'].'_min'] = '';
			}
		}
		function format_for_display($field, &$record, $fields){
			$record[$field['dataname']] = Date::get_timestamp(strtotime($record[$field['dataname']]), true);
		}
	}



	$customfield_types["datetime"]=new datetime_customfield_type(array(
		'fieldsql'=>'DATETIME NULL'
	));



	class function_customfield_type extends default_customfield_type{
		function format_for_form($field, &$record, $fields){
			$result_string='';

			if(!empty($field['function'])) {
				foreach($fields as $_field) {
					if($_field['datatype']=='number') {
						$field['function']=str_replace('{'.$_field['name'].'}', Number::to_phpnumber($record[$_field['dataname']]), $field['function']);
					}
				}
				$field['function']=preg_replace('/\{[^}]*\}/', '0', $field['function']);
				//go_debug($fields[$i]['function']);
				@eval("\$result_string=".$field['function'].";");
			}

			$record[$field['dataname']]=Number::format($result_string);
		}
	}



	$customfield_types["function"]=new function_customfield_type(array());

	
class user_customfield_type extends default_customfield_type {
	function format_for_display($field, &$record, $fields) {
		global $GO_MODULES;
		if(!empty($record[$field['dataname']])) {

			if($GLOBALS['GO_MODULES']->has_module('users') && !defined('EXPORTING')){
				$record[$field['dataname']]='<a href="#" onclick=\'GO.linkHandlers[8].call(this,'.
					$this->get_id($record[$field['dataname']]).');\' title="'.$this->get_name($record[$field['dataname']]).'">'.
						$this->get_name($record[$field['dataname']]).'</a>';
			}else
			{
				$record[$field['dataname']]=$this->get_name($record[$field['dataname']]);
			}
		}
	}

	private function get_id($cf) {
		$pos = strpos($cf,':');
		return substr($cf,0,$pos);
	}

	private function get_name($cf) {
		$pos = strpos($cf,':');
		return htmlspecialchars(substr($cf,$pos+1), ENT_COMPAT,'UTF-8');
	}
}
	$customfield_types["user"]=new user_customfield_type(array());


class customfields extends db {

	var $CF_MODEL_TYPES = array(
		'1' => 'GO\\Calendar\\Model\\Event',
		'2' => 'GO\\Addressbook\\Model\\Contact',
		'3' => 'GO\\Addressbook\\Model\\Company',
		'4' => 'GO\\Notes\\Model\\Note',
		'5' => 'GO\\Projects\\Model\\Project',
		'6' => 'GO\\Files\\Model\\File',
		'7' => 'GO\\Billing\\Model\\Order',
		'8' => 'GO\\Base\\Model\\User'
	);
	
	var $CF_TABLES = array(
		'1' => 'cal_events',
		'2' => 'ab_contacts',
		'3' => 'ab_companies',
		'4' => 'no_notes',
		'5' => 'pm_projects',
		'6' => 'fs_files',
		'7' => 'bs_orders',
		'8' => 'go_users'
	);
	



	function get_javascript($link_type, $type_name, $user_id=0) {
		return '';

		global $GO_SECURITY, $GO_MODULES;


		if(!empty($user_id) || $GLOBALS['GO_MODULES']->modules['customfields']['read_permission'])// && utf8_basename($_SERVER['PHP_SELF']) != 'singlemodule.php')
		{
			if(empty($user_id))
				$user_id=$GLOBALS['GO_SECURITY']->user_id;

			$javascript=  '

				if(!GO.customfields)
				{
					Ext.namespace("GO.customfields");
					GO.customfields.types={};
					GO.customfields.columns={};
				}

				GO.customfields.columns["'.$link_type.'"]=[];


				GO.customfields.types["'.$link_type.'"]={
					name: "'.$type_name.'",
					panels: []
				};';


			$cf = new customfields();

			$cf->get_authorized_categories($link_type, $user_id);
			while($cf->next_record()) {
				$fields = $this->get_category_fields($cf->f('id'));
				if(count($fields)) {
					$javascript .= 'GO.customfields.types["'.$link_type.'"].panels.push({xtype : "customformpanel", itemId:"cf-panel-'.$cf->f('id').'", category_id: '.$cf->f('id').', title : "'.htmlspecialchars($cf->f('name'),ENT_QUOTES, 'UTF-8').'", customfields : '.json_encode($fields).'});';

					foreach($fields as $field) {
						$align = $field['datatype']=='number' || $field['datatype']=='date' ? 'right' : 'left';
                                                $exclude_from_grid = $field['exclude_from_grid'] ? 'true' : 'false';
						$javascript .= 'GO.customfields.columns["'.$link_type.'"].push({'.
								'header: "'.String::escape_javascript($field['name']).'",'.
								'dataIndex: "'.$field['dataname'].'" ,'.
								'cfDataType:"'.$field['datatype'].'", '.
								'align:"'.$align.'", '.
								'sortable:true,'.
								'id: "'.$field['dataname'].'",'.
                                                                'exclude_from_grid: "'.$exclude_from_grid.'",'.
								'hidden:true});';
					}
				}
			}


			return $javascript;
		}

	}


	function get_next_category_sort_index($type) {
		$index = 1;
		$sql = "SELECT max(sort_index) AS sort_index FROM cf_categories WHERE extends_model='".$this->escape(addslslashes($this->CF_MODEL_TYPES[$type]))."'";
		$this->query($sql);
		if($this->next_record()) {
			$index += $this->f('sort_index');
		}
		return $index;
	}

	function create_table($type) {
		
		$model = get_model_by_type_id($type);
		$table = 'cf_'.$model->tableName();
		
		$sql = "CREATE TABLE IF NOT EXISTS `$table` (
		  `link_id` int(11) NOT NULL,
		  PRIMARY KEY  (`link_id`)
		) TYPE=MyISAM;";
		return $this->query($sql);
	}

	function resort_categories($type) {
		$cf = new customfields();

		$new_sort_index = 1;

		$this->get_categories($type);
		while($this->next_record(DB_ASSOC)) {
			$category['id'] = $this->f('id');
			$category['sort_index'] = $new_sort_index;

			$cf->update_category($category);
			$new_sort_index++;
		}
		return true;
	}


	function move_category_up($category_id) {
		$existing_category = $this->get_category($category_id);

		$category['id'] = $existing_category['id'];
		$category['sort_index'] = $existing_category['sort_index']-1;

		if($category_to_move_down = $this->get_category_by_sort_order($existing_category['type'], $category['sort_index'])) {
			$this->update_category($category);
			$update_category['sort_index']= $existing_category['sort_index'];
			$update_category['id']=$category_to_move_down['id'] ;
			$this->update_category($update_category);
			return true;
		}
		return false;
	}

	function get_category_by_sort_order($type, $sort_index) {
		$sql = "SELECT * FROM cf_categories WHERE extends_model='".$this->escape(($this->CF_MODEL_TYPES[$type]))."' AND sort_index=".$this->escape($sort_index);
		$this->query($sql);
		if($this->next_record(DB_ASSOC)) {
			return $this->record;
		}
		return false;
	}

	function get_category_by_name($type, $name) {
		$sql = "SELECT * FROM cf_categories WHERE extends_model='".$this->escape(($this->CF_MODEL_TYPES[$type]))."' AND name='".$this->escape($name)."'";
		$this->query($sql);
		if($this->next_record(DB_ASSOC)) {
			return $this->record;
		}
		return false;
	}

	function add_category($category) {
		$category['id'] = $this->nextid('cf_categories');
		$category['sort_index'] = $this->get_next_category_sort_index($category['type']);
		if($category['id'] && $this->insert_row('cf_categories', $category)) {
			return $category['id'];
		}
		return false;
	}

	function update_category($category) {
		return $this->update_row('cf_categories', 'id', $category);
	}

	function delete_category($category_id) {
		$cf =new customfields();

		$this->get_fields($category_id);
		while($this->next_record()) {
			$cf->delete_field($this->f('id'));
		}
		return $this->query("DELETE FROM cf_categories WHERE model_id=".intval($category_id));
	}

	function get_category($category_id) {
		$sql = "SELECT * FROM cf_categories WHERE id=".intval($category_id);
		$this->query($sql);
		if($this->next_record()) {
			return $this->record;
		}
		return false;
	}

	function get_categories($type) {
		$sql = "SELECT * FROM cf_categories WHERE extends_model='".$this->escape(($this->CF_MODEL_TYPES[$type]))."' ORDER BY sort_index ASC";
		$this->query($sql);
		return $this->num_rows();
	}

	function get_authorized_categories($type, $user_id) {
		$user_id = intval($user_id);
		
		$model = get_model_by_type_id($type);

		$sql ="SELECT DISTINCT c.* ".
				"FROM cf_categories c ".
				"INNER JOIN go_acl a ON c.acl_id=a.acl_id ".
				"LEFT JOIN go_users_groups ug ON a.group_id=ug.group_id ".
				"WHERE extends_model='".$this->escape(($model->className()))."' AND (a.user_id=".intval($user_id)." ".
				"OR ug.user_id=".intval($user_id).") ".
				" ORDER BY c.sort_index ASC";
		$this->query($sql);
		return $this->num_rows();
	}

	function get_datatype($type){
		global $customfield_types;

		if(isset($customfield_types[$type])){
			return $customfield_types[$type];
		}else
		{
			return $customfield_types['text'];
		}
	}


	function add_field($field) {

		$datatype = $this->get_datatype($field['datatype']);

		$field['id'] = $this->nextid('cf_fields');
		$field['sort_index'] = $this->get_next_field_sort_index($field['category_id']);
		if($field['id'] && $this->insert_row('cf_fields', $field)) {
			$category = $this->get_category($field['category_id']);
			
				$model = get_model_by_type_id($category['extends_model']);
				$table = 'cf_'.$model->tableName();
			
			$table = 'cf_'.$category['type'];
			$sql = "ALTER TABLE `".$table."` ADD `col_".$field['id']."` ".$datatype->fieldsql.";";

			$this->query($sql);

			return $field['id'];
		}
		return false;
	}

	function update_field($field) {

		$oldfield = $this->get_field($field['id']);

		if(isset($field['datatype']) && $oldfield['datatype']!=$field['datatype']) {

			//todo
			if($oldfield['datatype']=='select') {
				$this->query("DELETE FROM cf_select_options WHERE field_id=".$field['id']);
			}

			$category = $this->get_category($oldfield['category_id']);
			$table = 'cf_'.$category['type'];

			$datatype = $this->get_datatype($field['datatype']);

			$sql = "ALTER TABLE `".$table."` CHANGE `col_".$field['id']."` `col_".$field['id']."` ".$datatype->fieldsql;
			$this->query($sql);
		}

		return $this->update_row('cf_fields', 'id', $field);
	}

	function delete_field($field_id) {
		$field = $this->get_field($field_id);

		
		$this->query("DELETE FROM cf_select_options WHERE field_id=".$this->escape($field_id));
		$this->query("DELETE FROM cf_tree_select_options WHERE field_id=".$this->escape($field_id));

		$category = $this->get_category($field['category_id']);
		$table = 'cf_'.$category['type'];

		$sql = "ALTER TABLE `".$table."` DROP `col_".$field['id']."`";
		$this->halt_on_error='no';
		$this->query($sql);
		$this->halt_on_error='yes';

		return $this->query("DELETE FROM cf_fields WHERE model_id=".intval($field_id));
	}

	function get_field($field_id) {
		$sql = "SELECT * FROM cf_fields WHERE id=$field_id";
		$this->query($sql);
		if($this->next_record()) {
			return $this->record;
		}
		return false;
	}

	function get_next_field_sort_index($category_id) {
		$index = 1;
		$sql = "SELECT max(sort_index) AS sort_index FROM cf_fields WHERE category_id=".intval($category_id);
		$this->query($sql);
		if($this->next_record()) {
			$index += $this->f('sort_index');
		}
		return $index;
	}

	function resort_fields($category_id) {
		$cf = new customfields();

		$new_sort_index = 1;

		$this->get_fields($category_id);
		while($this->next_record(DB_ASSOC)) {
			$field['id'] = $this->f('id');
			$field['sort_index'] = $new_sort_index;

			$cf->update_field($field);
			$new_sort_index++;
		}
		return true;
	}


	function move_field_up($field_id) {
		$existing_field = $this->get_field($field_id);

		$field['id'] = $existing_field['id'];
		$field['sort_index'] = $existing_field['sort_index']-1;

		if($field_to_move_down = $this->get_field_by_sort_order($existing_field['category_id'], $field['sort_index'])) {
			$this->update_field($field);
			$update_field['sort_index']= $existing_field['sort_index'];
			$update_field['id']=$field_to_move_down['id'] ;
			$this->update_field($update_field);
			return true;
		}
		return false;
	}

	function get_field_by_sort_order($category_id, $sort_index) {
		$sql = "SELECT * FROM cf_fields WHERE category_id=".intval($category_id)." AND sort_index=".$this->escape($sort_index);
		$this->query($sql);
		if($this->next_record(DB_ASSOC)) {
			return $this->record;
		}
		return false;
	}

	function get_field_by_name($category_id, $name) {

		$sql = "SELECT * FROM cf_fields WHERE name='".$this->escape($name)."' AND category_id=".intval($category_id);

		$this->query($sql);
		if($this->next_record(DB_ASSOC)) {
			return $this->record;
		}
		return false;
	}
	
	function field_labels_to_datanames($type, $category_name, $label_value_array){
			$cat = $this->get_category_by_name($type, $category_name);
			
			$ret = array();
			foreach($label_value_array as $label=>$value){
				$cf = $this->get_field_by_name($cat['id'], $label);
				
				$label_value_array['col_'.$cf['id']]=$value;
			}
			
			return $label_value_array;
			
	}

	function find_first_field_by_name($link_type, $name, $category_name='') {
		$sql = "SELECT f.* FROM cf_fields f ".
				"INNER JOIN cf_categories c ON f.category_id=c.id ".
				"WHERE f.name='".$this->escape($name)."' AND c.extends_model=".$this->escape(($link_type));

		if(!empty($category_name)){
			$sql .= " AND c.name = '".$this->escape($category_name)."'";
		}

		$this->query($sql);
		if($this->next_record(DB_ASSOC)) {
			return $this->record;
		}
		return false;
	}

	function get_all_fields($link_type) {
		$sql = "SELECT f.*, c.name AS category_name ".
				"FROM cf_fields f ".
				"INNER JOIN cf_categories c ON f.category_id=c.id ".
				"WHERE c.extends_model=".$this->escape(($link_type))." ORDER BY c.sort_index ASC, f.sort_index ASC";
		$this->query($sql);
		return $this->num_rows();
	}


	function get_fields($category_id=0) {
		$sql = "SELECT * FROM cf_fields";

		if($category_id>0) {
			$sql .=" WHERE category_id=".intval($category_id);
		}

		$sql .= " ORDER BY sort_index ASC";
		$this->query($sql);
		return $this->num_rows();
	}

	function format_record(&$record, $link_type, $for_display=false, &$human_names=false) {

		if(!isset($this->cached_fields[$link_type])) {
			$this->cached_fields[$link_type]=array();
			$this->get_all_fields($link_type);
			while($f=$this->next_record()) {
				$f['dataname']='col_'.$f['id'];
				$this->cached_fields[$link_type][]=$f;
			}
		}

		$fields = $this->cached_fields[$link_type];

		/*$number_map=array();
		for($i=0;$i<count($fields);$i++) {
			if(!isset($fields[$i]['dataname'])) {
				$fields[$i]['dataname']='col_'.$fields[$i]['id'];
			}
			if($fields[$i]['datatype']=='number') {
				$number_map['{'.$fields[$i]['name'].'}']=$record[$fields[$i]['dataname']];
			}
		}*/

		$human_names=array();
		
		for($i=0;$i<count($fields);$i++) {
			$datatype = $this->get_datatype($fields[$i]['datatype']);

			if($for_display)
			{
				$datatype->format_for_display($fields[$i], $record, $fields);
			}else
			{
				$datatype->format_for_form($fields[$i], $record, $fields);
			}

			

			$human_name = $fields[$i]['name'];
			$count=2;
			while(isset($human_names[$human_name])){
				$human_name = $fields[$i]['name'].$count;
				$count++;
			}
			if(isset($record[$fields[$i]['dataname']])){
				$human_names[$human_name]=$record[$fields[$i]['dataname']];
				if(isset($record[$fields[$i]['dataname'].'_text'])){
					$human_names[$human_name.'_text']=$record[$fields[$i]['dataname'].'_text'];
				}
			}
		}
	}


	function get_fields_with_values($user_id, $link_type, $link_id, $for_display=false) {
		$table = 'cf_'.$this->CF_TABLES[$link_type];

		$fields = $this->get_authorized_fields($user_id, $link_type);

		$this->cached_fields[$link_type]=$fields;

		$sql = "SELECT * FROM $table WHERE model_id=".intval($link_id);
		$this->query($sql);
		$record = $this->next_record();
		if($record) {
			$this->format_record($record, $link_type, $for_display);

			for($i=0;$i<count($fields);$i++) {
				$fields[$i]['value'] = $record[$fields[$i]['dataname']];
			}
			return $fields;

		}else {
			$sql = "INSERT INTO $table (model_id) VALUES ('$link_id');";
			$this->query($sql);
			return $this->get_fields_with_values($user_id, $link_type, $link_id);
		}
	}

	function get_values($user_id, $link_type, $link_id, $for_display=false, $formatted=true) {

		/*$fields = $this->get_fields_with_values($user_id, $link_type, $link_id);
		$return_values['link_id']=$link_id;

		foreach($fields as $field) {
			$return_values[$field['dataname']]=$field['value'];
		}
		return $return_values;*/
		
		$model = get_model_by_type_id($link_type);
		if(!$model)
			return array();
		
		$table = 'cf_'.$model->tableName();

		$sql = "SELECT * FROM $table WHERE model_id=".intval($link_id);
		$this->query($sql);
		$record = $this->next_record();
		if($record) {
			if($formatted)
				$this->format_record($record, $link_type, $for_display);
		}else{
			$sql = "INSERT INTO $table (model_id) VALUES ('".intval($link_id)."');";
			$this->query($sql);
			return $this->get_values($user_id, $link_type, $link_id, $for_display, $formatted);
		}

		return $record;
	}

	function insert_cf_row($type, $link_id) {
		
		$model = get_model_by_type_id($type);

		$table = 'cf_'.$model->tableName();

		$sql = "INSERT IGNORE INTO $table (model_id) VALUES ('$link_id');";
		return $this->query($sql);
	}

	function delete_cf_row($type, $link_id){
		
		$model = get_model_by_type_id($type);
		$table = 'cf_'.$model->tableName();
		
		$sql = "DELETE FROM $table WHERE model_id=".intval($link_id);
		return $this->query($sql);
	}


	/*function get_all_fields_with_values($user_id, $link_type, $link_id)
	{
		$cf = new customfields();

		$fields=array();
		$values = $this->get_values($link_type, $link_id);

		$sql ="SELECT DISTINCT f.*, c.name AS category_name ".
			"FROM cf_fields f ".
			"INNER JOIN cf_categories c ON c.id=f.category_id ".
			"INNER JOIN go_acl a ON c.acl_id=a.acl_id ".
			"LEFT JOIN go_users_groups ug ON a.group_id=ug.group_id ".
			"WHERE c.type=$link_type AND (a.user_id=".intval($user_id)." OR ug.user_id=".intval($user_id).") ".
			" ORDER BY c.name ASC";
		$this->query($sql);

		while($this->next_record())
		{
			$field=array(
				'id'=>$this->f('id'),
				'label'=>$this->f('name'),
				'name'=>'col_'.$this->f('id'),
				'datatype'=>$this->f('datatype'),
				'category_id'=>$this->f('category_id'),
				'category_name'=>$this->f('category_name'),
				'value'=>$this->format_field($this->f('datatype'), $values['col_'.$this->f('id')])
			);

			$fields[]=$field;
		}
		return $fields;
	}
	*/
	function get_authorized_fields($user_id, $link_type) {
		$cf = new customfields();

		$user_id=intval($user_id);
		$link_type=$this->CF_MODEL_TYPES[$this->escape($link_type)];

		$fields=array();

		$sql ="SELECT DISTINCT f.*, c.name AS category_name ".
				"FROM cf_fields f ".
				"INNER JOIN cf_categories c ON c.id=f.category_id ".
				"INNER JOIN go_acl a ON c.acl_id=a.acl_id ".
				"LEFT JOIN go_users_groups ug ON a.group_id=ug.group_id ".
				"WHERE c.extends_model='".addslashes($link_type)."' AND (a.user_id=".intval($user_id)." OR ug.user_id=".intval($user_id).") ".
				" ORDER BY c.name ASC";
		$this->query($sql);

		while($field=$this->next_record()) {
			$field['dataname']='col_'.$this->f('id');

			if($field['datatype']=='select') {
				$field['options']=array();
				$cf->get_select_options($field['id']);

				while($cf->next_record()) {
					$field['options'][]=array($cf->f('text'));
				}
			}

			$fields[]=$field;
		}
		return $fields;
	}

	function get_category_fields($category_id) {
		$cf = new customfields();

		$this->get_fields($category_id);
		$fields = array();
		while($field=$this->next_record()) {
			$field['dataname']='col_'.$this->f('id');

			/*if($field['datatype']=='select') {
				$field['options']=array();
				$cf->get_select_options($field['id']);

				while($cf->next_record()) {
					$field['options'][]=array($cf->f('text'));
				}
			}*/

			$fields[]=$field;
		}
		return $fields;
	}


	function get_all_fields_with_values($user_id, $link_type, $link_id) {
		global $lang;
		$cf = new customfields();

		$values = $this->get_values($user_id, $link_type, $link_id, true);

		$this->get_authorized_categories($link_type, $user_id);

		$categories=array();


		while($category = $this->next_record()) {
			$category['fields']=array();
			$cf->get_fields($category['id']);
			while($cf->next_record()) {
				/*if($cf->f('datatype') == 'checkbox') {
					$values['col_'.$cf->f('id')]=empty($values['col_'.$cf->f('id')]) ? $lang['common']['no'] : $lang['common']['yes'];
				}*/

				if(!empty($values['col_'.$cf->f('id')])) {
					$field['name']=$cf->f('name');
					$field['value']=$values['col_'.$cf->f('id')];
					$category['fields'][]=$field;
				}
			}
			if(count($category['fields'])) {
				$categories[]=$category;
			}
		}

		return $categories;

	}


	/*function get_fields_table($category_id, $link_id=0, $disabled_fields=array(), $values_unset=false, $required_fields=array())
	{
		$category = $this->get_category($category_id);
		if($link_id==0 && isset($_POST['customfields']))
		{
			foreach($_POST['customfields']as $id=>$value)
			{
				$values['col_'.$id]=($value);
			}
		}else {
			$values = $link_id>0 ? $this->get_values($category['type'], $link_id) : array();
		}




		if($this->get_fields($category_id))
		{
			$table = new table();

			while($this->next_record())
			{
				$row = new table_row();
				if($this->f('datatype')=='checkbox' && !$values_unset)
				{

					$value = isset($values['col_'.$this->f('id')]) ? $values['col_'.$this->f('id')] : '';
					$input = $this->get_input_field($this->record, $value, $values_unset);

					if(in_array('col_'.$this->f('id'), $disabled_fields))
					{
						$input->set_attribute('disabled', 'true');
					}
					$cell = new table_cell($input->get_html());
					$cell->set_attribute('colspan','2');
					$row->add_cell($cell);
				}elseif($this->f('datatype')=='function')
				{
					$result_string='';
					if(trim($this->f('function'))!='')
					{
						$calc_array=explode(" ",$this->f('function'));
						foreach ($calc_array as $val){

							if($val{0}=="f")
							{//echo $values['col_'.ltrim($val, "f")].'<br>';
								$value = $values['col_'.ltrim($val, "f")];
								if(empty($value))
								{
									$value=0;
								}
							}else {
								$value=$val;
							}
							$result_string.=$value;
						}

						//echo $result_string;
						eval("\$result_string=$result_string;");
					}

					$cell = new table_cell($this->f('name').':');
					$cell->set_attribute('valign','top');
					$row->add_cell($cell);
					$row->add_cell(new table_cell(format_number($result_string)));
				}elseif($this->f('datatype')=='heading')
				{
					$cell = new table_cell($this->f('name'));
					$cell->set_attribute('colspan','2');
					$row->add_cell($cell);
				}else {

					$required = in_array($this->f('id'),$required_fields);

					if($required)
					{
						$end = '*:';
					}else
					{
						$end = ':';
					}

					$cell = new table_cell($this->f('name').$end);
					$cell->set_attribute('valign','top');
					$row->add_cell($cell);
					$value = isset($values['col_'.$this->f('id')]) ? $values['col_'.$this->f('id')] : '';



					$input = $this->get_input_field($this->record, $value, $values_unset,$required);

					if(in_array('col_'.$this->f('id'), $disabled_fields))
					{
						if($this->f('datatype')=='date')
						{
							$ipput->disabled=true;
						}else {
							$input->set_attribute('disabled', 'true');
						}

					}
					$row->add_cell(new table_cell($input->get_html()));
				}
				$table->add_row($row);
			}
			return $table;
		}
		return false;
	}*/


	function update_fields($user_id, $link_id, $link_type, $post_values, $insert=false, $only_update_provided_fields=false) {
		$fields = $this->get_authorized_fields($user_id, $link_type);

		$values['link_id']=$link_id;

		foreach($post_values as $key=>$value){
			if(substr($key,0,4)=='col_' && is_array($value))
				$post_values[$key]=implode('|',$value);
		}

		foreach($fields as $field) {			
			if(!$only_update_provided_fields || isset($post_values[$field['dataname']])){
				
				if(!empty($field['validation_regex']) && !empty($post_values[$field['dataname']])){
					if(!preg_match('/'.$field['validation_regex'].'/',$post_values[$field['dataname']])){
						throw new Exception($field['name'].' is ongeldig');
					}
				}
				
				$datatype = $this->get_datatype($field['datatype']);
				$datatype->format_for_database($field, $values, $post_values);
			}
		}
		
		$model = get_model_by_type_id($link_type);
		$table = 'cf_'.$model->tableName();

		if($insert) {
			return $this->insert_row($table, $values);
		}else {
			return $this->update_row($table, 'link_id', $values);
		}
	}

	function get_posted_fields($user_id, $link_type) {
		$fields = $this->get_authorized_fields($user_id, $link_type);

		$return = array();

		foreach($fields as $field) {

			switch($field['datatype']) {
				case 'checkbox':
					$field['value'] = isset($_POST[$field['dataname']]) ? '1' : '0';
					break;
				case 'datetime':
				case 'date':
					$field['value'] = Date::to_db_date(trim($_POST[$field['dataname']]));
					break;

				case 'number':
					$field['value'] = Number::to_phpnumber(trim($_POST[$field['dataname']]));
					break;
				case 'heading':

					break;
				case 'function':
					break;

				default:
					$field['value'] = isset($_POST[$field['dataname']]) ? (trim($_POST[$field['dataname']])) : '';
					break;
			}
			$return[$field['dataname']]=$field;
		}

		return $return;
	}


	/*function save_fields($category_id, $link_id, $disabled_fields=array(), $update_only_non_empty=false)
	{

		$category = $this->get_category($category_id);
		$table = 'cf_'.$category['type'];

		$this->get_fields($category_id);

		$values['link_id'] = $link_id;

		while($this->next_record())
		{
			if(!in_array('col_'.$this->f('id'), $disabled_fields))
			{
				//if(isset($_POST['customfields'][$this->f('id')]) && (!$update_only_non_empty || $_POST['customfields'][$this->f('id')]!=''))
				if(!$update_only_non_empty || (isset($_POST['customfields'][$this->f('id')]) && $_POST['customfields'][$this->f('id')]!=''))
				{
					switch($this->f('datatype'))
					{
						case 'checkbox':
							$values['col_'.$this->f('id')] = isset($_POST['customfields'][$this->f('id')]) ? $_POST['customfields'][$this->f('id')] : '0';
							break;

						case 'date':
							$values['col_'.$this->f('id')] = Date::to_db_date(trim($_POST['customfields'][$this->f('id')]));
							break;

						case 'number':
							$values['col_'.$this->f('id')] = Number::to_phpnumber(trim($_POST['customfields'][$this->f('id')]));
							break;

						default:
							$values['col_'.$this->f('id')] = (trim($_POST['customfields'][$this->f('id')]));
							break;
					}
				}
			}

		}
		return $this->update_row($table, 'link_id', $values);

	}
	*/



	/*function format_field($field, $value, $checkbox_as_lang=true)
	{
		switch($field['datatype'])
		{
			case 'function':

						$result_string='';

						if(!empty($field['function']))
						{
							foreach($fields as $_field)
							{
								if($_field['datatype']=='number')
								{
									$fields[$i]['function']=str_replace('{'.$_field['label'].'}', floatval($this->f($_field['name'])), $fields[$i]['function']);
								}
							}
							$fields[$i]['function']=preg_replace('/\{[^}]*\}/', '0', $fields[$i]['function']);
							eval("\$result_string=".$fields[$i]['function'].";");
						}

						$fields[$i]['value']=Number::format($result_string);

						break;
				case 'date':
					return empty($value) || $value='0000-00-00' ? '' : Date::format($this->f($value), false);
					break;

			case 'number':
				return Number::format($value);
				break;

			case 'textarea':
				return String::text_to_html($value);
				break;


			case 'checkbox':
				if($checkbox_as_lang){
					if ($value=='1')
					{
						return $GLOBALS['lang']['common']['yes'];
					}else {
						return $GLOBALS['lang']['common']['no'];
					}
					break;
				}

			default:
				return $value;
				break;
		}
	}*/


	function add_select_option($select_option) {
		$select_option['id']=$this->nextid('cf_select_options');

		if(!isset($select_option['sort_order'])){
			$sql = "SELECT MAX(`sort_order`) AS sort_order FROM cf_select_options WHERE field_id=?";
			$this->query($sql, 'i', $select_option['field_id']);
			$r = $this->next_record();

			$select_option['sort_order'] = $r['sort_order']+1;
		}

		if($select_option['id'] > 0) {
			if($this->insert_row('cf_select_options', $select_option)) {
				return $select_option['id'];
			}
		}
		return false;
	}

	function delete_other_select_options($field_id, $ids)
	{
		$sql = "DELETE FROM cf_select_options WHERE field_id=".$this->escape($field_id);
		if(count($ids))
		{
			$sql .= " AND id NOT IN (".implode(',', $ids).")";
		}
		$this->query($sql);
	}


	function delete_select_option($select_option_id) {
		$this->query("DELETE FROM cf_select_options WHERE model_id='".$this->escape($select_option_id)."';");
	}

	function update_select_option($select_option) {
		return $this->update_row('cf_select_options', 'id', $select_option);
	}

	function get_select_options($field_id) {

		$sql = "SELECT * FROM cf_select_options WHERE field_id=".intval($field_id)." ORDER BY sort_order ASC";


		$this->query($sql);
		return $this->num_rows();
	}

	function get_select_option($select_option_id) {
		$sql = "SELECT * FROM cf_select_options WHERE id='".$this->escape($select_option_id)."'";
		$this->query($sql);
		if($this->next_record(DB_ASSOC)) {
			return $this->record;
		}
		return false;
	}

	function delete_link_type($link_type) {
		$cf = new customfields();
		$cf2 = new customfields();

		$this->get_categories($link_type);
		while($this->next_record()) {
			$cat_id = $this->f('id');

			$cf->get_fields($cat_id);
			while($cf->next_record()) {
				$cf2->delete_field($cf->f('id'));
			}
		}

		return $this->query("DELETE FROM cf_categories WHERE extends_model=".$this->escape(($link_type)));
	}



	function get_tree_select_option_by_name($field_id, $parent_id, $name){
		$sql = "SELECT * FROM cf_tree_select_options WHERE parent_id=?";

		if($field_id>0){
			$sql .= " AND field_id=".intval($field_id);
		}
		$sql .= " AND name LIKE '".$this->escape($name)."'";
		$this->query($sql,'i', array($parent_id));
		return $this->next_record();
	}


	function get_tree_select_options($field_id, $parent_id){
		$sql = "SELECT * FROM cf_tree_select_options WHERE parent_id=?";

		if($field_id>0){
			$sql .= " AND field_id=".intval($field_id);
		}
		$sql .= " ORDER BY sort ASC";
		$this->query($sql,'i', array($parent_id));
		return $this->num_rows();
	}

	/**
	 * Add a Template tab
	 *
	 * @param Array $custom_report Associative array of record fields
	 *
	 * @access public
	 * @return int New record ID created
	 */
	function add_tree_select_option($tree_select_option, $skip_slaves=false)
	{
		$sql = "SELECT MAX(`sort`) AS sort FROM cf_tree_select_options WHERE field_id=?";
		$this->query($sql, 'i', $tree_select_option['field_id']);
		$r = $this->next_record();
		
		$tree_select_option['name']=str_replace(':','-', $tree_select_option['name']);

		$tree_select_option['sort'] = intval($r['sort'])+1;

		$this->insert_row('cf_tree_select_options', $tree_select_option);
		$id = $this->insert_id();

		if(!$skip_slaves)
			$this->set_tree_select_nesting_level($tree_select_option['field_id']);

		return $id;
	}
	/**
	 * Update a template tab
	 *
	 * @param Array $custom_report Associative array of record fields
	 *
	 * @access public
	 * @return bool True on success
	 */
	function update_tree_select_option($tree_select_option)
	{
		if(isset($tree_select_option['name']))
			$tree_select_option['name']=str_replace(':','-', $tree_select_option['name']);
		return $this->update_row('cf_tree_select_options', 'id', $tree_select_option);
	}

	function delete_tree_select_option($tree_select_option_id){

		$cf=new customfields();

		$cf->get_tree_select_options(0, $tree_select_option_id);
		while($o = $cf->next_record()){
			$this->delete_tree_select_option($o['id']);
		}
		
		return $this->query("DELETE FROM cf_tree_select_options WHERE id=?", 'i', $tree_select_option_id);
	}

	function set_tree_select_nesting_level($field_id){

		//treemaster_field_id
		$field = $this->get_field($field_id);

		$master_name = $field['name'];

		$field['datatype']='GO_Customfields_Customfieldtype_TreeselectSlave';
		$field['treemaster_field_id']=$field_id;
		
		$levels = $this->get_tree_select_nesting_level($field_id);
		$cf = new customfields();

		for($i=1;$i<$levels;$i++){
			$sql = "SELECT * FROM cf_fields WHERE treemaster_field_id=? AND nesting_level=?";
			$this->query($sql,'ii',array($field_id, $i));

			$r = $this->next_record();
			if(!$r){
				$field['name']=$master_name.' '.$i;
				$field['nesting_level']=$i;
				$this->add_field($field);
			}
		}		
	}

	function get_tree_select_nesting_level($field_id, $tree_select_option_id=0, $nesting_level=0){
		$cf=new customfields();
		$cf->get_tree_select_options($field_id, $tree_select_option_id);
		$start_nesting_level=$nesting_level;
		while($o = $cf->next_record()){
			$new_nesting_level=$this->get_tree_select_nesting_level(0, $o['id'], $start_nesting_level+1);
			if($new_nesting_level>$nesting_level){
				$nesting_level=$new_nesting_level;
			}
		}
		return $nesting_level;
	}

	/**
	 * This function collects a number of table records and some of their custom
	 * fields.
	 * @param int $typeId The id of the custom field type. E.g., 1 for events, 2
	 * for contacts, 3 for companies. See the top of this file for the mappings.
	 * @param array $condition Array where the keys become the complete strings
	 * at the left of the '=' sign in the WHERE part of the query, and the values
	 * become the complete strings at the right hand side of the '='. The WHERE
	 * conditions are conjucted with 'AND'.
	 * @param array $customfields The array of custom fields to be selected. Keys
	 * are the column names before an 'AS' combiner, values become the columns'
	 * alias of your choice (at the right hand side of an 'AS' combiner).
	 * @param int $limit Maximum number of records to be selected.
	 * @param boolean $random Whether or not to randomize the selection.
	 * @return boolean/int Returns the number of rows on success, returns false
	 * on failure. 
	 */
	public function get_regular_records($typeId, array $condition,array $customfields,$limit=false,$random=false) {
		$tableName = $this->CF_TABLES[$typeId];
		
		$sql = "SELECT $tableName.* ";
		foreach ($customfields as $columnName => $fieldName)
			$sql .= ", cf_".$tableName.".".$this->escape($columnName)." AS ".$this->escape($fieldName);

		$sql .= " FROM $tableName "
					. " INNER JOIN cf_$tableName ON cf_$tableName.model_id = $tableName.id";
		
		$sql .= " WHERE 1 ";
		foreach ($condition as $columnName => $columnValue)
			$sql .= " AND ".$this->escape($columnName)."='".$this->escape($columnValue)."' ";

		if(!empty($random))
			$sql .= " ORDER BY rand() ASC";
		if(!empty($limit))
			$sql .= " LIMIT 0,".intval($limit);
		$this->query($sql);
		
		if ($this->num_rows()===false)
			return false;
		else
			return $this->num_rows();
	}
}
