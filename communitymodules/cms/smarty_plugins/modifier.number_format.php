<?php
function smarty_modifier_number_format($number, $decimals = 2)
{
    return Number::format($number, $decimals);
}
?>