<?php

define('GO_CONFIG_FILE', "/media/sf_Projects/groupoffice-6.2/config.php");

require('../www/GO.php');

GO::language()->setLanguage('en');

chdir(dirname(__dir__));


$cmd = 'find ./www/modules -path '.escapeshellarg("*/language/*.php");
exec($cmd, $files);

$map = array();
$count = 0;
foreach ($files as $file62) {
	
	if(basename($file62) == 'en.php') {
		continue;
	}
	
	echo $file62."\n";

	$parts = explode("/", $file62);
	$module = $parts[count($parts) - 3];

	if ($module == "language") {
		$module = $parts[count($parts) - 2];
	}

	

	$file63 = str_replace('6.2', '6.3', realpath($file62));
	
	if(!file_exists($file63)) {
		continue;
	}
	
	$l = [];
	require($file62);
	$old = $l;

	$l = $bak = require($file63);

	foreach ($old as $key => $value) {
		if($key == "description" || $key == "name") {
			continue;
		}
		$newKey = GO::t($key, $module);
		
		if(!is_string($newKey) || strpos($newKey, "\n") !== false) {
			continue;
		}
		if (!isset($l[$newKey])) {
			$l[$newKey] = $value;
		}
	}
	
	var_dump(array_diff(array_keys($l), array_keys($bak)));
	
	$data = "<?php\nreturn " . var_export($l, true) . ";";			
	file_put_contents($file63, $data);
}
	
