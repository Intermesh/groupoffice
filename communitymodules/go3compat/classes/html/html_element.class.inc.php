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
 * @version $Id: html_element.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.html
 */


/**
 * Create any HTML element used as base for an easy way to create select and input elements with PHP.
 *
 * @author Merijn Schering <mschering@intermesh.nl> 
 * @package go.html
 * @access public
 */

class html_element
{
	var $name = '';		
	var $value = '';		
	var $attributes = array();
	var $tagname ='';
	var $innerHTML='';
	var $outerHTML='';
	var $lb = "";
	var $processed=false;
	
	function html_element($tagname, $innerHTML='', $attributes = array())
	{
		$this->tagname = $tagname;
		$this->innerHTML=$innerHTML;

        foreach ($attributes as $key => $value) {

            $this->set_attribute($key, $value);

        }

	}
	
	function set_linebreak($lb)
	{
		$this->lb=$lb;
	}
	
	function set_attribute($name, $value)
	{
		$this->attributes[$name]=$value;
	}		
	
	function add_html_element(&$element)
	{
		$this->innerHTML .= $element->get_html();
	}
	
	function add_outerhtml_element(&$element)
	{
		$this->outerHTML .= $element->get_html();
	}
	
	function set_tooltip($tooltip, $show_event='onmouseover', $hide_event='onmouseout')
	{
		$this->set_attribute($show_event, $tooltip->show_command);
		$this->set_attribute($hide_event, $tooltip->hide_command);
	}
	
	function get_html()
	{
		if(!$this->processed)
		{
			$this->processed=true;
				
			$this->outerHTML .= '<'.$this->tagname;
			foreach($this->attributes as $name=>$value)
			{
				$this->outerHTML .= ' '.$name.'="'.htmlspecialchars($value).'"';
			}
			
			if($this->innerHTML == '')
			{
				$this->outerHTML .= ' />';
			}else
			{
				$this->outerHTML .= '>'.$this->innerHTML.'</'.$this->tagname.'>'.$this->lb;
			}
		}
		return $this->outerHTML;
	}
	
	function print_html()
	{
		echo $this->get_html();
	}
}
