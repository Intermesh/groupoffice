#!/usr/bin/php
<?php

$commitMsg='translate update';

$root = 'svn+ssh://svn@svn.intermesh.nl/groupoffice-pro/branches/modules-6.0';
exec('svn ls '.$root, $output, $ret);

if($ret!=0)
	exit(var_dump($output));

$go_root = file_exists('GO.php') ? dirname(__FILE__) : dirname(__FILE__).'/www';

$wd = $go_root.'/modules';
chdir($wd);



foreach($output as $module){
	
	if(substr($module,-1)=='/'){ //check if it's a directory
				
		if(is_dir($module)){
			echo "COMMIT ".rtrim($module,'/')."\n";
			$cmd = 'svn ci -m "'.$commitMsg.'" '.$module;
			system($cmd, $ret);
		}

		
	}
}

echo "All done!\n";

