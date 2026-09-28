<?php
// Config of the mp-e2e server instance (tests/e2e/compose.yaml). Overlay-mounted
// over www/config.php inside that container only.
//
// db_name is what run.sh guards on: both instances share one MariaDB, so the
// database name — not the host — is what tells them apart.
$config['db_name'] = "mpserver";
$config['db_host'] = "mpdb";
$config['db_user'] = "root";
$config['db_pass'] = "root";
$config['db_port'] = 3306;

$config['file_storage_path'] = "/var/lib/groupoffice/";
$config['tmpdir'] = "/tmp/groupoffice/";

// Off, so the API answers the way it would in production: no stack traces in
// the JSON the other instance parses.
$config['debug'] = false;
