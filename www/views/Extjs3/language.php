<?php
require("../../GO.php");


$language = new \GO\Base\Language();
$lang = $language->getAllLanguage();



$l = $lang['base']['common'];
$l['countries'] = $lang['base']['countries'];
unset($lang['base']);
$l=array_merge($l, $lang);

echo 'GO={};';

echo 'GO.lang='.json_encode($l);
