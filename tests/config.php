<?php
// Each container gets its own test database, so checkouts that share the database server don't overwrite each other's.
// It's derived from the database of the container, eg. "groupoffice_develop_phpunit". "groupoffice" gives "groupoffice_phpunit".
$config['db_name'] = (getenv('MYSQL_DATABASE') ?: 'groupoffice') . '_phpunit';
//$config['db_host'] = "mysql8"; //mysql 8
//$config['db_host'] = "mysql57"; //mysql 5.7
$config['db_host'] = "db"; //mariadb
$config['db_user'] = "root";
$config['db_pass'] = "groupoffice";
// Hashing with the default cost takes about 0.2 seconds, and the tests create many users with a password
$config['passwordHashCost'] = 4;
