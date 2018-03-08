<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: csv_export_query.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

class csv_export_query extends base_export_query
{
	var $extension='csv';
	var $list_separator=';';

	var $text_separator='"';

	function __construct()
	{
		parent::__construct();

		if (isset($_SESSION['GO_SESSION']['list_separator']))
			$this->list_separator=$_SESSION['GO_SESSION']['list_separator'];
		if (isset($_SESSION['GO_SESSION']['text_separator']))
			$this->text_separator=$_SESSION['GO_SESSION']['text_separator'];
	}

	function download_headers()
	{
		$browser = detect_browser();
		header("Content-type: text/x-csv;charset=UTF-8");
		if ($browser['name'] == 'MSIE')
		{
			header('Content-Disposition: inline; filename="'.$this->title.'.csv"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Pragma: no-cache');
			header('Content-Disposition: attachment; filename="'.$this->title.'.csv"');
		}
	}

	function fputcsv($fp, $record, $ls, $ts){

		foreach($record as $key=>$value)
			$record[$key]=str_replace($ts, '\\'.$ts, $value);

		$data = $ts.implode($ts.$ls.$ts, $record).$ts."\r\n";

		return fputs($fp, $data);

		/*if(empty($ts)){
			$data = implode($ls, $record)."\r\n";
			return fputs($fp, $data);
		}else
		{
			
			return fputcsv($fp, $record, $ls, $ts);
		}*/
	}

	function export($fp){

		//don't export with thousands separator
		$old_sep = $_SESSION['GO_SESSION']['thousands_separator'];
		$_SESSION['GO_SESSION']['thousands_separator']='';

		parent::export($fp);

		global $lang, $GO_MODULES, $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
		$GO_USERS = new GO_USERS();

		if(count($this->headers))
			$this->fputcsv($fp, $this->headers, $this->list_separator, $this->text_separator);


		while($record = $this->db->next_record())
		{
			if(!count($this->columns))
			{
				foreach($record as $key=>$value)
				{
					$this->columns[]=$key;
					$this->headers[]=$key;
				}
				$this->fputcsv($fp, $this->headers, $this->list_separator, $this->text_separator);
			}

			/*if(is_array($this->q) && isset($this->q['method']))
			{
				call_user_func_array(array($this->q['class'], $this->q['method']),array(&$record, $cf));
			}*/
			$this->format_record($record);

			if(isset($record['user_id']) && isset($this->columns['user_id']))
			{
				$record['user_id']=$GO_USERS->get_user_realname($record['user_id']);
			}
			$values=array();
			foreach($this->columns as $index)
			{
				$values[] = $record[$index];
			}
			$this->fputcsv($fp, $values,$this->list_separator, $this->text_separator);

			//fclose($fp);
		}

		//restore thousands separator
		$_SESSION['GO_SESSION']['thousands_separator']=$old_sep;
	}
}
