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

function smarty_function_child_categories($params, &$smarty)
{
	global $co;

	
	return $co->print_child_categories($params, $smarty);


}
?>