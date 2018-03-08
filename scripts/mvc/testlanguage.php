<?php
require('../../www/GO.php');

$languages = \GO::language()->getLanguages();

foreach($languages as $iso=>$name){
	\GO::language()->setLanguage($iso);
	$lang = \GO::language()->getAllLanguage();
}

echo 'YES!';