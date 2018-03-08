<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: html_export_query.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

class html_export_query extends base_export_query
{
	var $extension='html';
	var $list_separator=';';

	var $text_separator='"';


	function download_headers()
	{
		//$browser = detect_browser();
		header("Content-type: text/html;charset=UTF-8");
		/*if ($browser['name'] == 'MSIE')
		{
			header('Content-Disposition: inline; filename="'.$this->title.'.html"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Pragma: no-cache');
			header('Content-Disposition: attachment; filename="'.$this->title.'.html"');
		}*/
	}

	function export($fp){

		parent::export($fp);

		global $lang, $GO_MODULES, $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
		$GO_USERS = new GO_USERS();

		fwrite($fp, '<html>
<head>
<title>'.$this->title.'</title>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<style>
body{
	font:12px helvetica;
}
table{
	border-collapse:collapse;
}
td, th{
	margin:0px;
	padding:1px 3px;
	font:12px helvetica;
}
th{
	white-space:nowrap;
	font-weight:bold;
}
</style>
</head>
<body>');

		fwrite($fp,'<h1>'.htmlspecialchars($this->title, ENT_COMPAT, 'UTF-8').'</h1>');

		fwrite($fp,'<table border="1">');

		if(count($this->headers)){
			fwrite($fp,'<tr>');
			for($i=0;$i<count($this->headers);$i++)
			{
				$align = in_array($this->columns[$i], $this->q['totalize_columns']) ? 'right' : 'left';
				fwrite($fp, '<th align="'.$align.'">'.htmlspecialchars($this->headers[$i], ENT_COMPAT, 'UTF-8').'</th>');
			}
			fwrite($fp,'</tr>');
		}


		while($record = $this->db->next_record())
		{
			$this->increase_totals($record);


			
			if(!count($this->columns))
			{
				foreach($record as $key=>$value)
				{
					$this->columns[]=$key;
					$this->headers[]=$key;
				}

				fwrite($fp,'<tr>');
				for($i=0;$i<count($this->headers);$i++)
				{
					$align = in_array($this->columns[$i], $this->q['totalize_columns']) ? 'right' : 'left';
					fwrite($fp, '<th align="'.$align.'">'.htmlspecialchars($this->headers[$i], ENT_COMPAT, 'UTF-8').'</th>');
				}				
				fwrite($fp,'</tr>');
			}

			$this->format_record($record);

			if(isset($record['user_id']) && isset($this->columns['user_id']))
			{
				$record['user_id']=$GO_USERS->get_user_realname($record['user_id']);
			}
			fwrite($fp,'<tr>');
			foreach($this->columns as $index)
			{
				$align = in_array($index, $this->q['totalize_columns']) ? 'right' : 'left';
				fwrite($fp, '<td align="'.$align.'">'.htmlspecialchars($record[$index], ENT_COMPAT, 'UTF-8').'</td>');
			}
			fwrite($fp,'</tr>');
			
		}

		if(isset($this->totals) && count($this->totals))
		{
			fwrite($fp, '<tr><td colspan="'.count($this->columns).'"><br /><b>'.$lang['common']['totals'].':</b></td></tr>');
			fwrite($fp, '<tr>');
			foreach($this->columns as $index)
			{
				$value = isset($this->totals[$index]) ? Number::format($this->totals[$index]) : '';
				fwrite($fp, '<td align="right">'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'</td>');
			}
			fwrite($fp, '</tr>');
		}

		fwrite($fp, '</table>');

		fwrite($fp, '</body></html>');
		//fclose($fp);
	}
}
