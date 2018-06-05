#!/usr/bin/php
<?php
if(PHP_SAPI != "cli") {
	die("Only on command line");
}

function parseDSN($dsn) {
    $dsn = substr($dsn, 6); //strip mysql:
    $parts = str_getcsv($dsn, ';');
    $options = [];
    foreach($parts as $part) {
      $is = strpos($part, '=');
      $options[substr($part,0, $is)] = substr($part, $is + 1);
    }
    
    return $options;
  }
	
$iniFile = $argv[1];

$ini = parse_ini_file($iniFile);

$dsn = parseDSN($ini['dsn']);

$config['db_name'] = $dsn['dbname'];
$config['db_host'] = $dsn['host'];
$config['db_user'] = $ini['username'];
$config['db_pass'] = $ini['password'];
$config['db_port'] = 3306;
$config['file_storage_path'] = $ini['dataPath'];
$config['tmpdir'] = $ini['tmpPath'];
$config['debug'] = false;

$php = "<?php\n\n\$config = ".var_export($config, true) .";\n\n";


$phpFile = dirname($iniFile) . '/config.php';

if(file_exists($phpFile)) {
	die($phpFile .' already exists. Please move it away.');
}

file_put_contents($phpFile, $php);