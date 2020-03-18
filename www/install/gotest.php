<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: gotest.php 22371 2018-02-13 14:17:26Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

$product_name = class_exists('GO') ? \GO::config()->product_name : 'Group-Office';

/**
* Format a size to a human readable format.
* 
* @param	int $size The size in bytes
* @param	int $decimals Number of decimals to display
* @access public
* @return string
*/

if(!function_exists('format_size'))
{
	function format_size($size, $decimals = 1) {
		switch ($size) {
			case ($size > 1073741824) :
				$size = number_format($size / 1073741824, $decimals, '.', ' ');
				$size .= " GB";
				break;
	
			case ($size > 1048576) :
				$size = number_format($size / 1048576, $decimals, '.', ' ');
				$size .= " MB";
				break;
	
			case ($size > 1024) :
				$size = number_format($size / 1024, $decimals, '.', ' ');
				$size .= " KB";
				break;
	
			default :
				number_format($size, $decimals, '.', ' ');
				$size .= " bytes";
				break;
		}
		return $size;
	}
}

function ini_is_enabled($name){
	$v = ini_get($name);
	
	return $v==1 || strtolower($v)=='on';
}

function ini_return_bytes($val) {
	
		$last = strtolower(substr(trim($val), -1));
	
		$val = (int)substr(trim($val), 0, -1);
		
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024*1024*1024;
        case 'm':
            $val *= 1024*1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function test_system(){

	global $product_name;
	
	$tests=array();


	

	$test['name']='PHP apcu support';
	$test['showSuccessFeedback'] = false;
	$test['pass']=extension_loaded('apcu');
	$test['feedback']="It's recommended to install the PHP apcu extension for Group-Office to improve performance.";
	$test['fatal']=false;
	$tests[]=$test;
	
	$test['name']='Operating System';
	$test['showSuccessFeedback'] = false;
	$test['pass']=strtolower(PHP_OS) === 'linux';
	$test['feedback']='Warning Your OS is "'.PHP_OS.'" The recommended OS is Linux. Other systems may work but are not officially supported';
	$test['fatal']=false;
	$tests[]=$test;
	
	
	$test['name']='Web server';
	$test['showSuccessFeedback'] = false;
	$test['pass']=stripos($_SERVER["SERVER_SOFTWARE"], 'apache') !== false;
	$test['feedback']="Warning, your web server ".$_SERVER["SERVER_SOFTWARE"]." is not officially supported";
	$test['fatal']=false;
	$tests[]=$test;
	
	
	$test['name']='PHP SAPI mode';
	$test['showSuccessFeedback'] = false;
	$test['pass']=php_sapi_name() != 'apache';
	$test['feedback']="Warning: PHP running in '".php_sapi_name()."' mode. This works fine but you need some additional rewrite rules for setting up activesync and CalDAV. See https://www.group-office.com/wiki/Z-push_2";
	$test['fatal']=false;
	$tests[]=$test;	
	
	
	$test['name']='Expose PHP';
	$test['showSuccessFeedback'] = false;
	$test['pass']=!ini_is_enabled('expose_php');
	$test['feedback']='Warning. You should set expose php to off to prevent version information to be public';
	$test['fatal']=false;
	$tests[]=$test;
	
	
	$test['name']='PHP version';
	$test['showSuccessFeedback'] = false;
	$test['pass']=function_exists('version_compare') && version_compare( phpversion(), "7.0", ">=");
	$test['feedback']='Fatal error: Your PHP version is too old to run '.$product_name.'. PHP 7.0 or higher is required';
	$test['fatal']=true;

//	$tests[]=$test;
//	$test['name']='Session Cookies';
//	$test['pass']=ini_get('session.cookie_httponly')!=1;
//	$test['feedback']='Warning: session.cookie_httponly is set to 1. We recommend setting it to 0 because this can give problems with file uploads.';
//	$test['fatal']=false;

	$tests[]=$test;

	$test['name']='Output buffering';
	$test['showSuccessFeedback'] = false;
	$test['pass']=!ini_is_enabled('output_buffering');
	$test['feedback']='Warning: output_buffering is enabled. This will increase memory usage might cause memory errors';
	$test['fatal']=false;

	$tests[]=$test;


	$test['name']='DOM extension';
	$test['showSuccessFeedback'] = false;
	$test['pass']=extension_loaded('dom');
	$test['feedback']='Error: The PHP xml / dom extension is required';
	$test['fatal']=true;

	$tests[]=$test;

	$test['name']='fileinfo extension';
	$test['showSuccessFeedback'] = false;
	$test['pass']=extension_loaded('fileinfo');
	$test['feedback']='Error: The PHP fileinfo extension is required';
	$test['fatal']=true;

	$tests[]=$test;
	
	
	$test['name']='intl';
	$test['showSuccessFeedback'] = false;
	$test['pass']=class_exists('Normalizer');
	$test['feedback']='Fatal: the php intl extension is required.';
	$test['fatal']=true;
	$tests[]=$test;

	//echo ini_get('mbstring.func_overload');

	$test['name']='mbstring function overloading';
	$test['showSuccessFeedback'] = false;
	$test['pass']=ini_get('mbstring.func_overload')<1;
	$test['feedback']='Warning: mbstring.func_overload is enabled in php.ini. Encrypting e-mail passwords will be disabled with this feature enabled. Disabling this feature is recommended';
	$test['fatal']=false;

	$tests[]=$test;


	$test['name']='Magic quotes setting';
	$test['showSuccessFeedback'] = false;
	$test['pass']=!get_magic_quotes_gpc();
	$test['feedback']='Warning: magic_quotes_gpc is enabled. You will get better performance if you disable this setting.';
	$test['fatal']=false;

	$tests[]=$test;

	
	$test['name']='PDO support';
	$test['showSuccessFeedback'] = false;
	$test['pass']=  class_exists('PDO') && extension_loaded('pdo_mysql');
	$test['feedback']='Fatal error: The PHP PDO extension with MySQL support is required.';
	$test['fatal']=true;

	$tests[]=$test;

	$test['name']='GD support';
	$test['showSuccessFeedback'] = false;
	$test['pass']=function_exists('getimagesize');
	$test['feedback']='Warning: No GD extension for PHP found. Without GD Group-Office can\'t create thumbnails.';
	$test['fatal']=false;

	$tests[]=$test;


	$test['name']='File upload support';
	$test['showSuccessFeedback'] = false;
	$test['pass']=ini_is_enabled('file_uploads');
	$test['feedback']='Warning: File uploads are disabled. Please set file_uploads=On in php.ini.';
	$test['fatal']=false;

	$tests[]=$test;
	
	$test['name']='File upload size';
	$test['showSuccessFeedback'] = false;
	$test['pass']= ini_return_bytes(ini_get('upload_max_filesize')) >= 20 * 1024 * 1024;
	$test['feedback']='Warning: The upload_max_filesize php.ini value is lower than 20MB ('.ini_get('upload_max_filesize').').  We recommend to settings this to at least 20MB';
	$test['fatal']=false;

	$tests[]=$test;
	

	$test['name']='Safe mode';
	$test['showSuccessFeedback'] = false;
	$test['pass']=!ini_is_enabled('safe_mode');
	$test['feedback']='Warning: safe_mode is enabled in php.ini. This may cause trouble with the filesystem module and Synchronization. If you can please set safe_mode=Off in php.ini';
	$test['fatal']=false;

	$tests[]=$test;

	$test['name']='Open base_dir';
	$test['showSuccessFeedback'] = false;
	$test['pass']=ini_get('open_basedir')=='';
	$test['feedback']='Warning: open_basedir is enabled. This may cause trouble with the filesystem module and Synchronization.';
	$test['fatal']=false;

	$tests[]=$test;

//	$test['name']='URL fopen';
//	$test['pass']=ini_is_enabled('allow_url_fopen');
//	$test['feedback']='Warning: allow_url_fopen is disabled in php.ini. RSS feeds on the start page will not work.';
//	$test['fatal']=false;

//	$tests[]=$test;
	
	$test['name']='Register globals';
	$test['showSuccessFeedback'] = false;
	$test['pass']=!ini_is_enabled('register_globals');
	$test['feedback']='Warning: register_globals is enabled in php.ini. This causes a problem in the spell checker and probably in some other parts. It\'s recommended to disable this.';
	$test['fatal']=false;

	$tests[]=$test;	

	$test['name']='zlib compression';
	$test['showSuccessFeedback'] = false;
	$test['pass']=extension_loaded('zlib');
	$test['feedback']='Warning: No zlib output compression support. You can increase the initial load time by installing this php extension.';
	$test['fatal']=false;

	$tests[]=$test;
	
	$test['name']='Calendar functions';
	$test['showSuccessFeedback'] = false;
	$test['pass']=function_exists('easter_date');
	$test['feedback']='Warning: Calendar functions not available. The '.$product_name.' calendar won\'t be able to generate all holidays for you. Please compile PHP with --enable-calendar.';
	$test['fatal']=false;

	$memory_limit = return_bytes(ini_get('memory_limit'));
	$tests[]=$test;
	$test['name']='Memory limit';
	$test['showSuccessFeedback'] = false;
//	$test['pass']=$memory_limit>=64*1024*1024;
	$test['pass']=($memory_limit<=0 || $memory_limit>=64*1024*1024);
	$test['feedback']='Warning: Your memory limit setting ('.format_size($memory_limit).') is less than 64MB. It\'s recommended to allow at least 64 MB.';
	$test['fatal']=false;

	$tests[]=$test;
	$test['name']='Error logging';
	$test['showSuccessFeedback'] = false;
	$test['pass']=ini_is_enabled('log_errors');
	$test['feedback']='Warning: PHP error logging is disabled in php.ini. It\'s recommended that this feature is enabled in a production environment.';
	$test['fatal']=false;

	/*$tests[]=$test;
	$test['name']='Error display';
	$test['pass']=ini_get('display_errors')!='1';
	$test['feedback']='Warning: PHP error display is enabled in php.ini. It\'s recommended that this feature is disabled because it can cause unnessecary interface crashes.';
	$test['fatal']=false;*/

	

	$tests[]=$test;
	$test['name']='MultiByte string functions';
	$test['pass']=function_exists('mb_detect_encoding');
	$test['feedback']='Warning: php-mbstring is not installed. Problems with non-ascii characters in e-mails and filenames might occur.';
	$test['fatal']=true;

	$tests[]=$test;
	$test['name']='CURL extension';
	$test['pass']=extension_loaded('curl');
	$test['feedback']='Warning: php-curl extension is required';
	$test['fatal']=true;

	$tests[]=$test;
	$test['name']='TAR Compression';
	$test['showSuccessFeedback'] = false;
	if(class_exists('GO'))
	{
		$tar = whereis('tar') ? whereis('tar') : \GO::config()->cmd_tar;
	}else
	{
		$tar = whereis('tar') ? whereis('tar') : '/bin/tar';
	}

	$test['pass']=@is_executable($tar);
	$test['feedback']='Warning: tar is not installed or not executable.';
	$test['fatal']=false;

	$tests[]=$test;
	$test['name']='ZIP Compression';
	$test['showSuccessFeedback'] = false;
	if(class_exists('GO'))
	{
		$zip = whereis('zip') ? whereis('zip') : \GO::config()->cmd_zip;
	}else
	{
		$zip = whereis('zip') ? whereis('zip') : '/usr/bin/zip';
	}
	$test['pass']=@is_executable($zip);
	$test['feedback']='Warning: zip is not installed or not executable. Unpacking zip archives and using document templates for Microsoft Word and Open-Office.org won\'t be possible.';
	$test['fatal']=false;

	$tests[]=$test;
	$test['name']='TNEF';
	$test['showSuccessFeedback'] = false;
	if(class_exists('GO'))
	{
		$tnef = whereis('tnef') ? whereis('tnef') : \GO::config()->cmd_tnef;
	}else
	{
		$tnef = whereis('tnef') ? whereis('tnef') : '/usr/bin/tnef';
	}
	$test['pass']=@is_executable($tnef);
	$test['feedback']='Warning: tnef is not installed or not executable. you can\'t view winmail.dat attachments in the email module.';
	$test['fatal']=false;

	$tests[]=$test;
	$test['name'] = 'pdfinfo';
	$test['showSuccessFeedback'] = false;
	if(class_exists('GO'))
	{
		$pdfinfo = whereis('pdfinfo') ? whereis('pdfinfo') : \GO::config()->cmd_pdfinfo; 
	}else
	{
		$pdfinfo = whereis('pdfinfo') ? whereis('pdfinfo') : '/usr/bin/pdfinfo'; // The debian default path for pdfinfo
	}
	$test['pass']=@is_executable($pdfinfo);
	$test['feedback']='Warning: pdfinfo is not installed or not executable. Please install the poppler-utils package';
	$test['fatal']=false;
	$tests[]=$test;
	
	$test['name']='Ioncube enabled';
	$test['pass']=$ioncubeWorks = ioncube_tester();
	$test['feedback']='Warning: Ioncube is not installed. The professional modules will not be enabled.';
	$test['fatal']=false;

	$tests[]=$test;
	
	
	$test['name']='Ioncube version check';
	
	$testResultArray = ioncube_version_tester();
	
	$test['showSuccessFeedback'] = true;
	$test['pass']= ($testResultArray['status'] == 'OK') ? true : false;	
	$test['feedback']= $testResultArray['problem'].(!empty($testResultArray['problem']) ? ' - ' : '').$testResultArray['solution'];
	$test['fatal']=false;
	
	$tests[]=$test;	
	
	
	$test['name']='JSON functions';
	$test['showSuccessFeedback'] = false;
	$test['pass']=function_exists('json_encode');
	$test['feedback']='Fatal error: json_encode and json_decode functions are not available. Try apt-get install php5-json on Debian or Ubuntu.';
	$test['fatal']=true;

	$tests[]=$test;


	$ze1compat=ini_get('zend.ze1_compatibility_mode');

	$test['name']='zend.ze1_compatibility_mode';
	$test['showSuccessFeedback'] = false;
	$test['pass']=empty($ze1compat);
	$test['feedback']='Fatal error: zend.ze1_compatibility_mode is enabled. '.$product_name.' can\'t run with this setting enabled';
	$test['fatal']=true;

	$tests[]=$test;	
	
	
	$url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['HTTP_HOST'];
	

	/*
	try {
		$headers = get_headers($url.'/caldav');	
	}catch(\Exception $e) {
		$headers = false;
	}
	$test['name']='CalDAV alias';
	$test['showSuccessFeedback'] = false;
	$test['pass']=$headers && (strpos($headers[0], '401')!==false || strpos($headers[0], '200')!==false);
	$test['feedback']="Note: The alias /caldav was not detected. Please create: Alias /caldav /groupoffice/modules/caldav/calendar.php.";
	$test['fatal']=false;

	$tests[]=$test;	
	
	try {
	$headers = get_headers($url.'/.well-known/caldav');	
	}catch(\Exception $e) {
		$headers = false;
	}
	
	$test['name']='CalDAV autodiscovery';
	$test['showSuccessFeedback'] = false;
	$test['pass']=$headers && (strpos($headers[0], '301')!==false|| strpos($headers[0], '200')!==false);
	$test['feedback']="Note: The redirect /.well-known/caldav was not detected. Please create a redirect: Redirect 301 /.well-known/caldav /caldav";
	$test['fatal']=false;

	$tests[]=$test;	
	
	try {
	$headers = get_headers($url.'/carddav');	
	}catch(\Exception $e) {
		$headers = false;
	}
	$test['name']='CardDAV alias';
	$test['showSuccessFeedback'] = false;
	$test['pass']=$headers && (strpos($headers[0], '401')!==false || strpos($headers[0], '200')!==false);;
	$test['feedback']="Note: The alias /carddav was not detected. Please create: Alias /carddav /groupoffice/modules/carddav/addressbook.php.";
	$test['fatal']=false;

	$tests[]=$test;	
	
	try {
	$headers = get_headers($url.'/.well-known/carddav');	
	}catch(\Exception $e) {
		$headers = false;
	}
	$test['name']='CardDAV autodiscovery';
	$test['showSuccessFeedback'] = false;
	$test['pass']=$headers && (strpos($headers[0], '301')!==false || strpos($headers[0], '200')!==false);
	$test['feedback']="Note: The redirect /.well-known/carddav was not detected. Please create a redirect: Redirect 301 /.well-known/carddav /carddav";
	$test['fatal']=false;

	$tests[]=$test;	
	
	try {
	$headers = get_headers($url.'/Microsoft-Server-ActiveSync');	
	}catch(\Exception $e) {
		$headers = false;
	}
	
//	var_dump($headers);
	$test['name']='Microsoft-Server-ActiveSync alias';
	$test['showSuccessFeedback'] = false;
	$test['pass']=$headers && (strpos($headers[0], '401')!==false || strpos($headers[0], '200')!==false);
	$test['feedback']="Note: The alias /Microsoft-Server-ActiveSync was not detected. Please create: Alias /Microsoft-Server-ActiveSync /groupoffice/modules/z-push/index.php.";
	$test['fatal']=false;

	$tests[]=$test;	
	
	*/

	$test['name']='MySQLnd driver';
	$test['showSuccessFeedback'] = false;
	$test['pass']= extension_loaded('mysqlnd');
	$test['feedback']= "PHP is not using the mysqlnd driver. Please install MySQLi.";
	$test['fatal']=true;
	

	$tests[]=$test;
	
	
	
	$test['name']='Shared Memory Functions';
	$test['showSuccessFeedback'] = false;
	$test['pass']= function_exists('sem_get') && function_exists('shm_attach') && function_exists('sem_acquire') && function_exists('shm_get_var');
	$test['feedback']= "InterProcessData::InitSharedMem(): PHP libraries for the use shared memory are not available. Z-push will work unreliably!";
	$test['fatal']=false;

	$tests[]=$test;
	
	
	$test['name']='Process Control Extensions';
	$test['showSuccessFeedback'] = false;
	$test['pass']= function_exists('posix_getuid');
	$test['feedback']= "Process Control Extensions PHP library not avaialble. Z-push will work unreliably!";
	$test['fatal']=false;
	
	$tests[]=$test;

	$test['name']='Floating point precision';
	$test['showSuccessFeedback'] = false;
	$test['pass']= ini_get("precision") >= 14 || ini_get("precision") == -1;
	$test['feedback']= "'precision' is set too low in php.ini. Please set it to 14 or higher";
	$test['fatal']=false;
	
	$tests[]=$test;
	
	
	
	
	if(class_exists('GO')){
		
//		$test['name']='Writable license file';
//		$test['pass']=GO::getLicenseFile()->exists() && GO::getLicenseFile()->isWritable();					
//		$test['feedback']="Fatal: the license file ".GO::getLicenseFile()->path()." is not writable. Please make it writable for the webserver.";
//		$test['fatal']=true;
//
//		$tests[]=$test;	
		
		$root = dirname(dirname(__FILE__));

		if($ioncubeWorks)
		{

			$test['name']='Professional license';
			$test['showSuccessFeedback'] = false;
	//		if(!file_exists(GO::config()->root_path.'groupoffice-pro-'.\GO::config()->getMajorVersion().'-license.txt')){
	//			$test['feedback']='Warning: There\'s no license file "groupoffice-pro-'.\GO::config()->getMajorVersion().'-license.txt" in the root of Group-Office. The professional modules will not be enabled.';
	//			$test['fatal']=false;
	//			$test['pass']=false;
	//		}else
			if(!\GO::scriptCanBeDecoded('Professional'))
			{
				$test['feedback']='Warning: Your professional license is invalid. The professional modules will not be enabled. Please contact Intermesh about this problem and supply the output of this page.';
				$test['fatal']=false;
				$test['pass']=false;
			}else
			{
				$test['feedback']='';
				$test['fatal']=false;
				$test['pass']=true;
			}	

			$tests[]=$test;
		}



		if(GO\Base\Db\Utils::tableExists('core_module'))
		{		
			$test['name']='Protected files path';
			$test['showSuccessFeedback'] = false;
			$test['pass']=is_writable(\GO::config()->file_storage_path);
			$test['feedback']='Fatal error: the file_storage_path setting in config.php is not writable. You must correct this or '.$product_name.' will not run.';
			$test['fatal']=false;
			$tests[]=$test;	

			$test['name']='Cronjob';
			$test['showSuccessFeedback'] = false;
			$test['pass']=GO::cronIsRunning();
			$test['feedback']="Warning: The main cron job doesn't appear to be running. Please add a cron job: \n\n* * * * * www-data php ".\GO::config()->root_path."cron.php ".\GO::config()->get_config_file();
			$test['fatal']=false;
			$tests[]=$test;	
		}	
	}
	
	return $tests;
}

function output_system_test(){
	global $product_name;

	$tests = test_system();
	
	$fatal = false;
	
	foreach($tests as $test)
	{
		echo '<p>'.$test['name'].': ';
		if(!$test['pass'])
		{
			echo '<span style="color:red">'.$test['feedback'].'</span>';
			
			if($test['fatal'])
				$fatal=true;
		}else
		{
			echo '<span style="color:green">OK</span>';
			
			if(!empty($test['showSuccessFeedback'])){
				echo ' <span style="color:green"><small>( '.$test['feedback'].' )</small></span>';
			}

		}
		
		echo '</p>';
	}	

	if($fatal)
	{
		echo '<p style="color:red">Fatal errors occured. '.$product_name.' will not run properly with current system setup!</p>';
	}else
	{
		echo '<p><b>Passed!</b> '.$product_name.' should run on this machine</p>';
	}
	
	
	echo '<table style="font:12px Arial"><tr>
	<td colspan="2">
	<br />
	<b>Use this information for your '.$product_name.' Professional license:</b>
	</td>
</tr>

<tr>
	<td valign="top">Server name:</td>
	<td>'.$_SERVER['SERVER_NAME'].'</td>
</tr>
<tr>
	<td valign="top">Server IP:</td>
	<td>'.gethostbyname($_SERVER['SERVER_NAME']).'</td>
</tr></table>';
	
	return !$fatal;
	
}


//
// Detect some system parameters
//
function ic_system_info()
{
	$thread_safe = false;
	$debug_build = false;
	$cgi_cli = false;
	$php_ini_path = '';

	ob_start();
	phpinfo(INFO_GENERAL);
	$php_info = ob_get_contents();
	ob_end_clean();

	foreach (explode("\n",$php_info) as $line) {
		if (stripos($line, 'command')!==false) {
			continue;
		}

		if (preg_match('/thread safety.*(enabled|yes)/Ui',$line)) {
			$thread_safe = true;
		}

		if (preg_match('/debug.*(enabled|yes)/Ui',$line)) {
			$debug_build = true;
		}

		if (preg_match("/configuration file.*(<\/B><\/td><TD ALIGN=\"left\">| => |v\">)([^ <]*)(.*<\/td.*)?/",$line,$match)) {
			$php_ini_path = $match[2];

			//
			// If we can't access the php.ini file then we probably lost on the match
			//
			if (!@file_exists($php_ini_path)) {
				$php_ini_path = '';
			}
		}

		$cgi_cli = ((strpos(php_sapi_name(),'cgi') !== false) ||
		(strpos(php_sapi_name(),'cli') !== false));
	}

	return array('THREAD_SAFE' => $thread_safe,
	       'DEBUG_BUILD' => $debug_build,
	       'PHP_INI'     => $php_ini_path,
	       'CGI_CLI'     => $cgi_cli);
}

/**
 * Get the ioncube version data
 * 
 * @return array
 */
function ioncube_loader_version_array () {
	$version = ioncube_loader_version();
	list($major, $minor) = explode('.', $version);
	return array('version'=>$version, 'major'=>$major, 'minor'=>$minor);
}

/**
 * Test Ioncube version
 * 
 * @return array
 */
function ioncube_version_tester(){
	
	$test = array(
		'status'	=>'OK',
		'problem'	=>'',
		'solution'=>'No additional configuration required.'
	);
	
	if(!ioncube_tester()){
		$test = array(
			'status'	=>'ERROR',
			'problem'	=>'',
			'solution'=>'Warning: Ioncube is not installed. The professional modules will not be enabled.'
		);
		
		return $test;
	}
	
	if(extension_loaded('ionCube Loader')){
		
		$ioncube_loader_version = ioncube_loader_version_array();

	  if ($ioncube_loader_version['major'] < 5 ) {// || ($ioncube_loader_version['major'] == 4 && $ioncube_loader_version['minor'] < 6) ) {
			
			$test = array(
				'status'	=>'ERROR',
				'problem'	=>'Installed: version '. $ioncube_loader_version['version'],
				'solution'=>'Ioncube loader is installed but needs to be updated. Group-Office will only work reliably with ioncube loader version 5.0 or later. The most recent version of the loader can be found <a href="http://www.ioncube.com/loaders.php" target="_blank">here</a>.'
			);
			
	  } else {
			
			$test = array(
				'status'	=>'OK',
				'problem'	=>'Installed: version '. $ioncube_loader_version['version'],
				'solution'=>'No additional configuration required.'
			);
			
	  }
	} else {
		$sys_info = ic_system_info();
		
		if ($sys_info['THREAD_SAFE'] && !$sys_info['CGI_CLI']) {
			$test = array(
				'status'	=>'ERROR',
				'problem'	=>'Your PHP install appears to have threading support and run-time loading is only possible on threaded web servers if using the CGI, FastCGI or CLI interface.',
				'solution'=>'To run encoded files please install the Loader in the php.ini file.'
			);
			
			return $test;
		}
		
		if ($sys_info['DEBUG_BUILD']) {
			$test = array(
				'status'	=>'ERROR',
				'problem'	=>'Your PHP installation appears to be built with debugging support enabled and this is incompatible with ionCube Loaders. Debugging support in PHP produces slower execution, is not recommended for production builds and was probably a mistake.',
				'solution'=>'You should rebuild PHP without the --enable-debug option.'
			);
			
			return $test;
		}
		
		if ( ini_get('safe_mode') ) {
			$test = array(
				'status'	=>'ERROR',
				'problem'	=>'PHP safe mode is enabled and run time loading will not be possible.',
				'solution'=>'To run encoded files please install the Loader in the php.ini file. Instructions can be found <a href="http://www.ioncube.com/loader_installation.php" target="_blank">here</a>. Alternatively contact your hosting provider or system administrator and ask them to enable safe mode for your account.'
			);
			
			return $test;
		}
		
		$test = array(
			'status'	=>'ERROR',
			'problem'	=>'',
			'solution'=>'Warning: Ioncube is not installed. The professional modules will not be enabled.'
		);
		
	}
	
	return $test;
}

function ioncube_tester()
{
	if(extension_loaded('ionCube Loader'))
	{
		return true;
	}

	//
	// Test some system info
	//
	$sys_info = ic_system_info();

	if ($sys_info['THREAD_SAFE'] && !$sys_info['CGI_CLI']) {
		return false;
	}

	if ($sys_info['DEBUG_BUILD']) {
		return false;
	}
	//
	// Check safe mode and for a valid extensions directory
	//
	if (ini_get('safe_mode') == '1') {
		return false;
	}


	// Old style naming should be long gone now
	$test_old_name = false;

	$_u = php_uname();
	$_os = substr($_u,0,strpos($_u,' '));
	$_os_key = strtolower(substr($_u,0,3));

	$_php_version = phpversion();
	$_php_family = substr($_php_version,0,3);

	$_loader_sfix = (($_os_key == 'win') ? '.dll' : '.so');

	$_ln_old="ioncube_loader.$_loader_sfix";
	$_ln_old_loc="/ioncube/$_ln_old";

	$_ln_new="ioncube_loader_${_os_key}_${_php_family}${_loader_sfix}";
	$_ln_new_loc="/ioncube/$_ln_new";


	$_extdir = ini_get('extension_dir');
	if ($_extdir == './') {
		$_extdir = '.';
	}

	$_oid = $_id = realpath($_extdir);

	$_here = dirname(__FILE__);
	if ((@$_id[1]) == ':') {
		$_id = str_replace('\\','/',substr($_id,2));
		$_here = str_replace('\\','/',substr($_here,2));
	}
	$_rd=str_repeat('/..',substr_count($_id,'/')).$_here.'/';

	if ($_oid === false) {
		return false;
	}


	$_ln = '';
	$_i=strlen($_rd);
	while($_i--) {
		if($_rd[$_i]=='/') {
			if ($test_old_name) {
				// Try the old style Loader name
				$_lp=substr($_rd,0,$_i).$_ln_old_loc;
				$_fqlp=$_oid.$_lp;
				if(@file_exists($_fqlp)) {
			  $_ln=$_lp;
			  break;
				}
			}
			// Try the new style Loader name
			$_lp=substr($_rd,0,$_i).$_ln_new_loc;
			$_fqlp=$_oid.$_lp;
			if(@file_exists($_fqlp)) {
				$_ln=$_lp;
				break;
			}
		}
	}

	//
	// If Loader not found, try the fallback of in the extensions directory
	//
	if (!$_ln) {
		if ($test_old_name) {
			if (@file_exists($_id.$_ln_old_loc)) {
				$_ln = $_ln_old_loc;
			}
		}
		if (@file_exists($_id.$_ln_new_loc)) {
			$_ln = $_ln_new_loc;
		}
	}

	if ($_ln) {
		if(function_exists('dl')){
			@dl($_ln);
		}
		if(extension_loaded('ionCube Loader')) {
			return true;
		} else {
			return false;
		}
	}
	return false;
}

function is__writable($path) {
	//will work in despite of Windows ACLs bug
	//NOTE: use a trailing slash for folders!!!
	//see http://bugs.php.net/bug.php?id=27609
	//see http://bugs.php.net/bug.php?id=30931

	if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
	return is__writable($path.uniqid(mt_rand()).'.tmp');
	else if (is_dir($path))
	return is__writable($path.'/'.uniqid(mt_rand()).'.tmp');
	// check tmp file for read/write capabilities
	$rm = file_exists($path);
	$f = @fopen($path, 'a');
	if ($f===false)
	return false;
	fclose($f);
	if (!$rm)
	unlink($path);
	return true;
}

function escape_config_value($value)
{
	return str_replace('\\"', '"', addslashes($value));
}

function save_config($config_obj)
{
	global $CONFIG_FILE;

	require($CONFIG_FILE);

	$values = get_object_vars($config_obj);

	foreach($values as $key=>$value)
	{
		if($key == 'version')
		break;
			
			
		if(!is_object($value))
		{
			$config[$key]=$value;
		}
	}


	$config_data = "<?php\n";
	foreach($config as $key=>$value)
	{
		if($value===true)
		{
			$config_data .= '$config[\''.$key.'\']=true;'."\n";
		}elseif($value===false)
		{
			$config_data .= '$config[\''.$key.'\']=false;'."\n";
		}else
		{
			$config_data .= '$config[\''.$key.'\']="'.$value.'";'."\n";
		}
	}
	return file_put_contents($CONFIG_FILE, $config_data);
}



function whereis($cmd)
{
	if(strtoupper(substr(PHP_OS, 0, 3)) != 'WIN' && @is_executable('whereis'))
	{
		exec('whereis '.$cmd, $return);

		if(isset($return[0]))
		{
			$locations = explode(' ', $return[0]);
			if(isset($locations[1]))
			{
				return $locations[1];
			}
		}
	}
	return false;
}


function systemIsOk() {
	$tests = test_system();
	
	foreach($tests as $test)
	{
		if(!$test['pass'] && $test['fatal']) {
			return false;
		}
	}
	
	return true;
}

function return_bytes($val) {

	$last = strtolower(substr(trim($val), -1));
	
	$val = (int)substr(trim($val), 0, -1);
	
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}
//
////check if we are included
if(!class_exists("go\core\App"))
{
	echo '<h1 style="font-family: Arial, Helvetica;font-size: 18px;">'.$product_name.' test script</h1><div style="font-family: Arial, Helvetica;font-size: 12px;"> ';
	output_system_test();
	echo "</div>";
}
