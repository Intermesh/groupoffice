#!/usr/bin/php
<?php
$root = 'svn+ssh://svn@svn.intermesh.nl/groupoffice-pro/branches/modules-6.2';
exec('svn ls '.$root, $output, $ret);

if($ret!=0)
	exit(var_dump($output));

$go_root = file_exists('GO.php') ? dirname(__FILE__) : dirname(__FILE__).'/www';

$wd = $go_root.'/modules';
chdir($wd);

foreach($output as $module){
	
	if(substr($module,-1)=='/'){ //check if it's a directory

		//exec('rm -Rf '.$module);

		//uncomment the following line if subversion is upgraded
//		system('svn upgrade '.$module);
				
		if(is_dir($module)){
			echo "UPDATE ".rtrim($module,'/')."\n";
			$cmd = 'svn up '.$module;
			
//			echo "REVERT ".rtrim($module,'/')."\n";
//			$cmd = 'svn revert '.$module.' -R';
		}else
		{
			echo "CHECKOUT ".rtrim($module,'/')."\n";
			$cmd = 'svn co '.$root.'/'.$module;
		}	
		

		system($cmd, $ret);
	}
}

echo "Updating SF.net working copy\n";

chdir($go_root);
system("svn up");

echo "All done!\n";
