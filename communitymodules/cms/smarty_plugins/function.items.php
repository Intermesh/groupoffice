<?php
function smarty_function_items($params, &$smarty)
{
	global $co;


	
	return $co->print_items($params, $smarty);


}
?>