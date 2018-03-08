<?php
function smarty_modifier_go_strip_tags($string, $allowable_tags='', $replace_with_space = true)
{
    if ($replace_with_space)
        $string = preg_replace('!<[^>]*?>!', '\\0 ', $string);
   
     return strip_tags($string,$allowable_tags);
}
?>