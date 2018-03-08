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
 * @version $Id: input.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.html
 */

/**
 * Required classes
 */
require_once($GLOBALS['GO_CONFIG']->class_path.'html/html_element.class.inc.php');

/**
 * Create an form input field
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.html
 * @access public
 */
class input extends html_element
{
	var $required=false;
	
	function get_post_value($name)
	{
		$name = str_replace(']','', $name);
		$var_names = explode('[',$name);

		for($i=0;$i<count($var_names);$i++)
		{
			if(isset($tmp))
			{
				if(!isset($tmp[$var_names[$i]]))
				{
					return false;
				}
				$tmp=$tmp[$var_names[$i]];
			}elseif(isset($_POST[$var_names[$i]])) {
				$tmp =$_POST[$var_names[$i]];
			}else {
				return false;
			}
		}

		if(isset($tmp))
		{
			return $tmp;
		}else {
			return false;
		}
	}
	
	function input($type, $name, $value='', $remind_value=true, $required=false)
	{
		$this->required=$required;
		$this->tagname = 'input';
		$this->set_linebreak("\n");
		
		$this->set_attribute('type', $type);
		$this->set_attribute('name', $name);
		$post_value = $this->get_post_value($name);
		if($remind_value && $post_value)
		{
			$this->set_attribute('value', htmlspecialchars($post_value));
		}else
		{
			$this->set_attribute('value', htmlspecialchars($value));
		}

		if($this->required && $_SERVER['REQUEST_METHOD']=='POST' && empty($post_value))
		{
			$this->set_attribute('class','textbox_error');
		}
	}
	
	function get_html()
	{	
		if(!isset($this->attributes['class']) && $this->attributes['type'] != 'hidden')
		{
			$this->set_attribute('class','textbox');
		}
		
		$html = '<input';				
		foreach($this->attributes as $name=>$value)
		{
			$html .= ' '.$name.'="'.$value.'"';
		}
		$html .= ' />'.$this->lb;
		
		return $html;
	}
}
