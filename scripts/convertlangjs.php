<?php

require('../www/GO.php');

GO::language()->setLanguage('en');

//$lang = GO::language()->getAllLanguage();
//$lang['base'] = $lang['base']['common'];
//$langMap = [];
//foreach($lang as $mod => $modLang) {
//	$langMap[$mod] = [];
//	foreach($modLang as $key => $value) {
//		if(is_string($value)) {
//			$langMap[$mod][$value] = $key;							
//		}
//	}
//}
//
//var_dump($GLOBALS['langMap']['base']);

$count = 0;

function replaceStr($orig, $match, $module = 'base') {
	$match = trim($match, '\'"');
	$module = trim($module, '\'"');
		
	$key = GO::t($match, $module);
	if(!is_string($key)) {
		$key = $match;
	}

	$replacement =  't("' . str_replace('"', '\\"', $key) .  '"';
	
	if($module != 'base') {
		$replacement .= ', "' . $module . '"';
	}
	
	$replacement .= ')';

	echo "Replace ".$orig.' with '. $replacement ."\n";
	
	$GLOBALS['count']++;

	return $replacement;
}

function replaceFile($file) {
	
	$GLOBALS['count'] = 0;
	echo $file . "\n";
	
	$content = file_get_contents($file);
	
	$count = 0;
	
	$content = preg_replace_callback('/GO::t\(([\'"a-zA-Z0-9_]+)\)/', function($matches) {				
		return replaceStr($matches[0], $matches[1]);
	}, $content);	
	
	$content = preg_replace_callback('/GO::t\(([\'"a-zA-Z0-9_]+\s*,\s*([\'"a-zA-Z0-9_]+))\)/', function($matches) {				
		return replaceStr($matches[0], $matches[1], $matches[2]);
	}, $content);	
	
	
	
	
	if($GLOBALS['count']) {
		echo "\nReplaced ".$GLOBALS['count']." tags\n\n";
		file_put_contents($file, $content);
	}
}




chdir (dirname(__dir__));
exec('find -name *.php ', $files);

foreach($files as $file) {
	replaceFile($file);
}
