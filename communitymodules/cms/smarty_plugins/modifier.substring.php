<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty substring modifier plugin
 *
 * Type:     modifier<br>
 * Name:     substring<br>
 * Purpose:  Substring a string
 * 
 * @author   Merijn Schering <mschering@intermesh.nl>
 * @param StringHelper
 * @param integer
 * @param StringHelper
 * @param boolean
 * @param boolean
 * @return StringHelper
 */
function smarty_modifier_substring($string, $start, $length=null)
{
	if(isset($length))
	{
		return substr($string, $start, $length);
	}else
	{
		return substr($string, $start);
	}
}
?>