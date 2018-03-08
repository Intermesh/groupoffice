<?php
function smarty_function_category_items($params, &$smarty)
{
	global $co;


	
	return $co->print_category_items($params, $smarty);


}
?>