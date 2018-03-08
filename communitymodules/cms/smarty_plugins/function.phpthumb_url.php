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
function smarty_function_phpthumb_url($params)
{
	global $GO_CONFIG;
	
	require_once($GO_CONFIG->control_path.'phpthumb/phpThumb.config.php');
	
	return phpThumbURL($params['params']);
}
?>