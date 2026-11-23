<?php
$config['db_name'] = "groupoffice_phpunit";
//$config['db_host'] = "mysql8"; //mysql 8
//$config['db_host'] = "mysql57"; //mysql 5.7
$config['db_host'] = "db"; //mariadb
$config['db_user'] = "root";
$config['db_pass'] = "groupoffice";
// Hashing with the default cost takes about 0.2 seconds, and the tests create many users with a password
$config['passwordHashCost'] = 4;
