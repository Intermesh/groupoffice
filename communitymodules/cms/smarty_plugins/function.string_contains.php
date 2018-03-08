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
 * Name:     string_contains<br>
 * Purpose:  Check if string contains target string
 * 
 * @author   Wilmar van Beusekom <wilmar@intermesh.nl>
 * @param StringHelper
 * @param StringHelper
 * @return boolean
 */

function smarty_function_string_contains($params)
{
	if (isset($params['return_bool']) && $params['return_bool'])
		return strpos($params['haystack'],$params['needle'])!==false;
	elseif (strpos($params['haystack'],$params['needle'])!==false)
		return 'true';
	else
		return 'false';
}
?>