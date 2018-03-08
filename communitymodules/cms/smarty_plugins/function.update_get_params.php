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
 * @author WilmarVB <wilmar@intermesh.nl>
 * 
 * This is part of the file filtering triplet smarty functions:
 * update_get_params, child_categories and sort_years. The first creates file
 * filtering hyperlinks for anchor tags in the filter menu's. The other two tags
 * search through the database to show the available category filters and year
 * filters, respectively.
 */

function smarty_function_update_get_params($params, &$smarty)
{
	$baseurl = $params['baseurl'];
	unset($params['baseurl']);
	
	$first = true;
	$GET = $_GET;
	
	
	// Update GET variables that match keys with parameter
	foreach ($GET as $k=>$v) {
		if (key_exists($k,$params)) {
			$GET[$k] = $params[$k];
			unset($params[$k]);
		}
		if (!$first)
			$get_string .= '&';
		
		$get_string .= $k.'='.$GET[$k];
		
		$first = false;
	}
	
	// Add new GET variables
	foreach ($params as $k=>$v) {
		if (!$first)
			$get_string .= '&';
		
		$get_string .= $k.'='.$v;
		
		$first = false;
	}
	
	$output = strpos($baseurl,'?')!==false ? $baseurl.'&'.$get_string : $baseurl.'?'.$get_string;
	
	$smarty->assign('updated_get_href',$output);

	echo $output;
}
?>