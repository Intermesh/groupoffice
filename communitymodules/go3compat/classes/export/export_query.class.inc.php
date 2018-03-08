<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: export_query.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */


//ini_set('display_errors', 'off');

class export_query
{
	function find_custom_exports(){
		global $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'filesystem.class.inc');
		$fs = new filesystem();

		$ce=array();
		if(is_dir($GLOBALS['GO_CONFIG']->file_storage_path.'customexports')){
			$files = $fs->get_files($GLOBALS['GO_CONFIG']->file_storage_path.'customexports');
			while($file = array_shift($files)){
				require_once($file['path']);

				$names = explode('.', $file['name']);

				$cls = new $names[0];

				if(!isset($ce[$cls->query]))
					$ce[$cls->query]=array();

				$ce[$cls->query][]=array('name'=>$cls->name, 'cls'=>$names[0]);
			}
		}

		return 'GO.customexports='.json_encode($ce).';';
	}	
}

class base_export_query{
	var $db;
	var $query_name;
	var $q;
	var $totals = array();
	var $title='';
	var $extension='';

	var $sql;
	var $params;
	var $types;

	function __construct(){

		$this->db = new db();
		
		if(isset($_REQUEST['query'])){
			$this->query_name=$_REQUEST['query'];
			$this->q = $_SESSION['GO_SESSION']['export_queries'][$_REQUEST['query']];
		}

		if(!isset($this->q['totalize_columns']))
			$this->q['totalize_columns']=array();
		
		if(isset($_REQUEST['title']))
			$this->title = $_REQUEST['title'];
	}

	function download_headers()
	{

	}

	function prepare_query(){
		$this->params = array();
		$this->types='';

		if(is_array($this->q))
		{
			if(!empty($this->q['require']))
			{
				require_once($this->q['require']);
			}

			$this->totals=array();
			if(isset($this->q['totalize_columns']))
			{
				foreach($this->q['totalize_columns'] as $column){
					$this->totals[$column]=0;
				}
			}else
			{
				unset($this->totals);
			}

			$extra_sql=array();
			$this->sql = $this->q['query'];
			if(isset($this->q['extra_params']))
			{
				foreach($this->q['extra_params'] as $param=>$this->sqlpart)
				{
					if(!empty($_REQUEST[$param]))
					{
						$this->params[] = $_REQUEST[$param];
						$extra_sql[]=$this->sqlpart;
					}
				}
			}
			if(count($this->params))
			{
				$insert = ' ';
				if(!strpos($this->sql, 'WHERE'))
				{
					$insert .= 'WHERE ';
				}else
				{
					$insert .= ' AND ';
				}
				$insert .= implode(' AND ', $extra_sql);

				$pos = strpos($this->sql, 'ORDER');

				if(!$pos)
				{
					$this->sql .= $insert;
				}else
				{
					$this->sql = substr($this->sql, 0, $pos).$insert.' '.substr($this->sql, $pos);
				}

				$this->types=str_repeat('s',count($this->params));
			}
		}else
		{
			$this->sql = $this->q;

			$this->params=array();
		}
	}

	function query(){
		$this->prepare_query();

		$GLOBALS['GO_EVENTS']->fire_event('export_before_query', array(&$this, &$this->sql, &$this->types, &$this->params));

		$this->db->query($this->sql,$this->types,$this->params);
	}

	function format_record(&$record){
		if(is_array($this->q) && isset($this->q['method']))
		{
			call_user_func_array(array($this->q['class'], $this->q['method']),array(&$record, $this->cf));
		}
		
		$GLOBALS['GO_EVENTS']->fire_event('export_format_record', array(&$this, &$record));
	}

	function init_columns(){
		$this->columns=array();
		$this->headers=array();
		if(isset($_REQUEST['columns']))
		{
			$indexesAndHeaders = explode(',', $_REQUEST['columns']);

			foreach($indexesAndHeaders as $i)
			{
				$indexAndHeader = explode(':', $i);

				if(!isset($this->q['hide_columns']) || !in_array($indexAndHeader[0], $this->q['hide_columns'])){
					$this->headers[]=$indexAndHeader[1];
					$this->columns[]=$indexAndHeader[0];
				}
			}
		}

		if(isset($this->q['extra_columns'])){
			foreach($this->q['extra_columns'] as $column){
				if(!isset($column['index']))
					$column['index']=count($this->headers);

				array_insert($this->headers,$column['header'], $column['index']);
				array_insert($this->columns,$column['column'], $column['index']);
			}
		}

		$GLOBALS['GO_EVENTS']->fire_event('export_init_columns', array(&$this));
	}

	function export($fp)
	{
		global $GO_MODULES;

		if($GLOBALS['GO_MODULES']->has_module('customfields')) {
			require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'].'customfields.class.inc.php');
			$this->cf = new customfields();
		}else
		{
			$this->cf=false;
		}

		$this->query();
		$this->init_columns();
	}


	function increase_totals($record){
		foreach($this->q['totalize_columns'] as $column){
			if(isset($record[$column]))
				$this->totals[$column]+=$record[$column];
		}
	}
}