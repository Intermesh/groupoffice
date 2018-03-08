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
 * @version $Id: GOXML.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.xml
 */

/**
 * Some useful functions to eaily read and create XML documents
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: GOXML.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @license AGPL Affero General Public License
 * @package go.xml
 * @uses db
 * @since Group-Office 3.0
 */
class GOXML
{
	var $dom;
	
	public function __construct(&$dom)
	{
		$this->dom = &$dom;
	}
	
	public function addArray($array, &$node)
	{
		if(!is_array($array))
			var_dump($array);
			
		foreach($array as $key=>$value){
			//echo $key."\n";
			if(is_int($key))
			{				

				if(isset($value['_name']))
				{
					/*
					 * array is in format:
					 * All optional except for _name
					 * 
					 * array('_name'=>'tagname',
					 * 	'_value'=>'text',
					 * 	'_children'=>'array(),
					 * 	'_cdata'=>'cdata');
					 */
					if(isset($value['_value']))
						$sub_node = $this->dom->createElement($value['_name'], $value['_value']);
					else
						$sub_node = $this->dom->createElement($value['_name']);
	
					if(isset($value['_cdata']))
					{
						$cdata = $this->dom->createCDATASection($value['_cdata']);
						$sub_node->appendChild($cdata);
					}
					
					if(isset($value['_attributes']))
					{
						foreach($value['_attributes'] as $name=>$att_value)
						{
								$sub_node->setAttribute($name, $att_value);
						}
					}
					$node->appendChild($sub_node);
					if(isset($value['_children']))
						$this->addArray($value['_children'], $sub_node);
				}else
				{
					//var_dump($value);
					$this->addArray($value, $node);
				}
									
			}elseif(is_array($value)){
				$sub_node = $this->dom->createElement($key);
				$node->appendChild($sub_node);
				$this->addArray($value, $sub_node);
			} else {

				$sub_node = $this->dom->createElement($key, $value);
				$node->appendChild($sub_node);
				
			}
		}
	}
	
	
	public function toArray($n)
	{		
		
		$xml_array=array();

		if(!is_object($n)){
			go_debug($n);
			return '';
		}
		
		if($n->hasChildNodes())
		{			
			if($n->childNodes->length == 1 && ($n->childNodes->item(0)->nodeType==XML_TEXT_NODE || $n->childNodes->item(0)->nodeType==XML_CDATA_SECTION_NODE))
			{
				//echo 'Return: '.$n->childNodes->item(0)->nodeValue."\n";
				return $n->childNodes->item(0)->nodeValue;				
			}else
			{				
				foreach($n->childNodes as $cn)
				{
					
					if($cn->nodeType==1)
					{
						//echo $cn->nodeName."\n";
						
							
							
						if(isset($xml_array[$cn->nodeName]) && (!is_array($xml_array[$cn->nodeName]) || key($xml_array[$cn->nodeName])!="0"))
						{
							//echo "1\n";							
							$xml_array[$cn->nodeName]=array($xml_array[$cn->nodeName]);							
						}
						
						if(isset($xml_array[$cn->nodeName]) && count($xml_array[$cn->nodeName]) && is_int(key($xml_array[$cn->nodeName])))
						{
							//echo "2\n";
							$xml_array[$cn->nodeName][]=$this->toArray($cn);
						}else
						{
							//echo "3\n";
							$xml_array[$cn->nodeName]=$this->toArray($cn);
						}						
					}
				}	
			}
			return $xml_array;	
		}else
		{
			return '';
		}
		
		
	}
}
?>