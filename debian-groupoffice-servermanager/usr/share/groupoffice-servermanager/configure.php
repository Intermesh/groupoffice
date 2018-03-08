#!/usr/bin/php
<?php
require('/etc/groupoffice/config-servermanager.inc.php');

chdir(dirname(__FILE__));

$replacements['db_name']=$dbname;
$replacements['db_user']=$dbuser;
$replacements['db_pass']=$dbpass;
$replacements['domain']=$wildcarddomain;

function create_file($file, $tpl, $replacements) {
	$data = file_get_contents($tpl);

	foreach($replacements as $key=>$value) {
		$data = str_replace('{'.$key.'}', $value, $data);
	}

	file_put_contents($file, $data);
}

function set_value($file, $str, $detect=false) {
	
	if(!$detect)
		$detect=$str;
	
	$data = file_get_contents($file);

	if(!strpos($data, $detect)) {
		$data .= $str."\n";
	}
	file_put_contents($file, $data);
}


echo 'Configuring apache'."\n";

if(file_exists('/etc/apache2/sites-enabled/000-groupoffice')){
	//for new apache 2.4 config
	rename('/etc/apache2/sites-enabled/000-groupoffice','/etc/apache2/sites-enabled/000-groupoffice.conf');
}
	

if(!file_exists('/etc/apache2/sites-enabled/000-groupoffice.conf'))
	create_file('/etc/apache2/sites-enabled/000-groupoffice.conf', 'tpl/etc/apache2/sites-enabled/000-groupoffice.conf', $replacements);

//if(file_exists('/etc/apache2/sites-enabled/000-default'))
//	unlink('/etc/apache2/sites-enabled/000-default');

echo "Configuring sudo\n";
set_value('/etc/sudoers','www-data ALL=NOPASSWD:/usr/share/groupoffice/groupofficecli.php');

set_value('/etc/groupoffice/config.php','$config["servermanager_wildcard_domain"]="'.$wildcarddomain.'";','servermanager_wildcard_domain');

set_value('/etc/groupoffice/config.php','$config["servermanager_trials_enabled"]=false;','servermanager_trials_enabled');

echo "Done!\n\n";