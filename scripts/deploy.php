#!/usr/bin/php
<?php
chdir(__DIR__);
$version = require "../www/version.php";
$versionParts = explode(".", $version);
$majorVersion = $versionParts[0].".".$versionParts[1];

$target = "/usr/local/share/groupoffice-" . $majorVersion;
$manageConfig = "/etc/groupoffice/manage/config.php";

echo "Updating sources\n";
system("./update-git.sh", $result);
if($result !== 0) {
    echo "\n\nFailed to update git repos!\n\n";
    exit(1);
}
system("./build.sh", $result);

if($result !== 0) {
	echo "\n\nFailed to build!\n\n";
	exit(1);
}

$rsyncCmd = "rsync -av --delete --exclude=.git ../ " . $target;
echo "Running: " . $rsyncCmd . "\n";
system($rsyncCmd, $result);

if($result !== 0) {
	echo "\n\nFailed to sync sources!\n\n";
	exit(1);
}

//CHANGE TO DIR WHERE MANAGER IS ON
chdir("/usr/local/share/groupoffice-26.0/www/");
$upgradeCmd = "sudo -u www-data php cli.php core/System/upgrade -c=".$manageConfig;
echo "Running: " . $upgradeCmd . "\n";
system($upgradeCmd, $result);

if($result !== 0) {
	echo "\n\nFailed to upgrade!\n\n";
	exit(1);
}

echo "Done\n\n";
