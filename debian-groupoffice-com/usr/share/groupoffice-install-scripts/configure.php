#!/usr/bin/php
<?php
echo 'Configuring Group-Office'."\n";

$config_file = '/etc/groupoffice/config.php';

if(!isset($config['file_storage_path'])){
	$config['file_storage_path']='/home/groupoffice/';
}
//if(file_exists($config_file))
//{
//	//don't overwrite an existing configuration. Create a file with date suffix.
//	$config_file = '/etc/groupoffice/config.php.'.date('Ymd');
//}
require('/etc/groupoffice/config-db.php');

chdir(dirname(__FILE__));

$replacements['db_name']=$dbname;
$replacements['db_user']=$dbuser;
$replacements['db_pass']=$dbpass;
//$replacements['domain']=$domain;

//sometimes the timezone file has multiple lines
$tz = trim(file_get_contents('/etc/timezone'));
$tzs = explode("\n",$tz);
$tzs[]="Europe/Amsterdam";
foreach($tzs as $timezone){
	$valid = @date_default_timezone_set($timezone);
	if($valid){
		$replacements['timezone']=$timezone;
		break;
	}
}

exec('locale',$output);

$eq_pos = strpos($output[0], '=');

if($eq_pos)
{
	$locale = substr($output[0],$eq_pos+1);
	$dot_pos = strpos($locale,'.');
	if($dot_pos)
	{
		$locale = substr($locale,0, $dot_pos);
	}
}else
{
	$locale = 'en_US';
}

$arr = explode('_', $locale);

if(isset($arr[1]))
{
	$replacements['lang']=$arr[0];
	$replacements['country']=$arr[1];
}else
{
	$replacements['lang']='en';
	$replacements['country']='NL';
}


function create_file($file, $tpl, $replacements) {
	$data = file_get_contents($tpl);

	foreach($replacements as $key=>$value) {
		$data = str_replace('{'.$key.'}', str_replace('\'','\\\'',$value), $data);
	}

	file_put_contents($file, $data);
}

function set_value($file, $str) {
	$data = file_get_contents($file);

	if(!strpos($data, $str)) {
		$data .= "\n".$str;
	}
	file_put_contents($file, $data);
}

if(!file_exists($config_file)) {
	create_file($config_file, 'tpl/config.php', $replacements);

	chgrp('/etc/groupoffice/config.php', 'www-data');
	chmod('/etc/groupoffice/config.php', 0644);
}

require_once('/etc/groupoffice/config.php');

echo "Setting cache permissions\n\n";

if(is_dir($config['tmpdir'].'cache'))
	system('chown -R www-data:www-data '.$config['tmpdir'].'cache');

if(is_dir($config['tmpdir'].'diskcache'))
	system('chown -R www-data:www-data '.$config['tmpdir'].'diskcache');


system('chown www-data:www-data '.$config['file_storage_path']);
system('chown www-data:www-data '.$config['file_storage_path'].'*');

if(is_dir($config['file_storage_path'].'log'))
	system('chown -R www-data:www-data '.$config['file_storage_path'].'log');

if(file_exists($config['file_storage_path'].'key.txt'))
	system('chown www-data:www-data '.$config['file_storage_path'].'key.txt');

if(file_exists($config['file_storage_path'].'defuse-crypto.txt'))
	system('chown www-data:www-data '.$config['file_storage_path'].'defuse-crypto.txt');

system('chown www-data /etc/groupoffice/config.php');

system('sudo -u www-data /usr/bin/php '.$config['root_path'].'install/autoinstall.php -c=/etc/groupoffice/config.php --adminpassword=admin --adminusername=admin --adminemail=admin@example.com');
system('sudo -u www-data  /usr/bin/php '.$config['root_path'].'groupofficecli.php -r=maintenance/upgrade -c=/etc/groupoffice/config.php');

system('chown root /etc/groupoffice/config.php');

//system('chown www-data /usr/share/groupoffice/groupoffice-license.txt');

//create symlink for site module public files

$publicLink = is_dir('/var/www/html') ? "/var/www/html/public" : "/var/www/public";

if(!file_exists($publicLink)){
	system('ln -s '.$config['file_storage_path'].'public '.$publicLink);
}


if(is_dir('/etc/apache2/conf-enabled') && file_exists('/etc/apache2/conf.d/groupoffice.conf')){
	system('mv /etc/apache2/conf.d/groupoffice.conf /etc/apache2/conf-enabled/groupoffice.conf');
	system('rmdir /etc/apache2/conf.d');
}

echo "Done!\n\n";
