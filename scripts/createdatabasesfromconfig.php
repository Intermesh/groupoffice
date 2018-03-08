<?php

$rootPassword = '';

$cmd = 'find /etc/groupoffice -type f -name config.php';
exec($cmd, $configFiles, $return_var);

//return var should be 0 otherwise something went wrong
if($return_var!=0)
	exit("Find command did not run successfully.\n");

foreach($configFiles as $configFile){
	
	$config = array();
	
	require($configFile);
	
	$sql = "CREATE DATABASE `".$config['db_name']."`;";	
	echo $sql."\n";
//	exec('mysql -u root -p'.escapeshellarg($rootPassword).' -e "'.  escapeshellarg($sql).'"');
	
	
	$sql = "GRANT ALL PRIVILEGES ON `".$config['db_user']."`.* TO '".$config['db_name']."'@'localhost' IDENTIFIED BY '".$config['db_pass']."' WITH GRANT OPTION;";	
	echo $sql."\n\n";
//	exec('mysql -u root -p'.$rootPassword).' -e "'.$sql.'"');
	
	
	
}