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
 * @version $Id: go_template_parser.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * Parses a template
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: go_template_parser.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @license AGPL Affero General Public License
 * @package go.utils
 * @uses db
 * @since Group-Office 3.0
 */
class go_template_parser
{
	var $open_tag_symbol = '&lt;';
	var $close_tag_symbol = '&gt;';
	
	var $tags = array('gotpl');
	var $fields;

	var $skip_empty=false;
	
	public function __construct($fields, $values, $skip_empty=false)
	{
		$this->fields=$fields;
		$this->values=$values;
		$this->skip_empty=$skip_empty;
				
	}
	
	private function get_tag($tag, $content) {
		$start_pos = strpos($content, $this->open_tag_symbol.$tag);
		if ($start_pos !== false) {
			$end_pos = $this->get_end_pos($tag, $content, $start_pos);
			$sub_start_pos = 	strpos($content, $this->open_tag_symbol.$tag, $start_pos+strlen($this->open_tag_symbol.$tag));
			
			if($sub_start_pos!== false)
			{
				$sub_end_pos = $end_pos;
				
				//echo $sub_start_pos.' < '.$sub_end_pos."\n---\n";
				
				while($sub_start_pos<$sub_end_pos)
				{
					$sub_end_pos = $this->get_end_pos($tag, $content, $sub_end_pos);
					$sub_start_pos = 	strpos($content, $this->open_tag_symbol.$tag, $sub_start_pos+strlen($this->open_tag_symbol.$tag));
					
					if($sub_end_pos)
						$end_pos = $sub_end_pos;
				}	
			}
			if($end_pos === false)
			{
				return false;
			}
			$tag_length = $end_pos-$start_pos;
			return substr($content, $start_pos, $tag_length);
		}
		return false;
	}
	
	
	private function get_end_pos($tag, $content, $offset=0)
	{
		$end_pos = strpos($content, $this->open_tag_symbol.'/'.$tag.$this->close_tag_symbol, $offset);
		if($end_pos!==false)
		{
			$end_pos+=strlen($this->open_tag_symbol.'/'.$tag.$this->close_tag_symbol);
		}
		return $end_pos;		
	}

	private function get_attributes($tag) {
		$attributes = array ();
		$in_value = false;
		$in_name = false;
		$name = '';
		$value = '';
		$length = strlen($tag);
		
		$exit=false;
		
		for ($i = 0; $i < $length; $i ++) {
			
			if($exit)
			{
				break;
			}
			$char = $tag[$i];
			switch ($char) {
				case '"' :
					if ($in_value) {
						$in_value = false;

						$attributes[trim($name)] = trim($value);
						$name = '';
						$value = '';
					} else {
						$in_value = true;
					}

					break;

				case ' ' :
					if (!$in_value) {
						$in_name = true;
					} else {
						$value .= $char;
					}
					break;

				case '=' :
					$in_name = false;
					if ($in_value) {
						$value .= $char;
					}
					break;

				default :
					if ($in_name) {
						$name .= $char;
					}

					if ($in_value) {
						$value .= $char;
					}
					break;
			}
		}
		return $attributes;
	}

	
	private function replace_template(&$content)
	{
		foreach($this->fields as $field)
		{
			if($this->skip_empty && empty($this->values[$field]))
				continue;
			
			$value = isset($this->values[$field]) ? $this->values[$field] : '';
			$content = str_replace('{'.$field.'}', $value, $content);
			$content = str_replace('%'.$field.'%', $value, $content);
		}
	}
	
	public function parse(&$content)
	{
		$this->parse_tags($content);
		$this->replace_template($content);
	}
	
	
	private function parse_tags(&$content)
	{
		
		foreach($this->values as $varname=>$value)
		{
			$$varname = $value;
		}
		
		foreach($this->tags as $tagname)
		{
			while ($tag = $this->get_tag($tagname, $content)) {
	
				$attributes = $this->get_attributes($tag);
				
				$print = false;
				//echo '$print = '.$attributes['if'].';'."\n\n";
				//eval('$print = '.html_entity_decode($attributes['if']).';');
				
				$print = !empty($$attributes['if']);
				
				
				//var_dump($print);
				if($print)
				{
					$start_pos = strpos($tag, $this->close_tag_symbol);					
					$tagcontent = substr($tag, $start_pos+strlen($this->close_tag_symbol));					
					$tagcontent = substr($tagcontent,0, strlen($tagcontent)-strlen($this->open_tag_symbol.'/'.$tagname.$this->close_tag_symbol));	
					$this->parse_tags($tagcontent);					
				}else
				{
					$tagcontent = '';
				}
				
			//	echo $tagcontent;
				
				//echo "\n\n########\n\n";
				
				$content = str_replace($tag, $tagcontent, $content);
			}
		}
		
	}
	
}
?>