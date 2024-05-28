#!/usr/bin/php
<?php
chdir(__DIR__);
$version = require "../www/version.php";
$versionParts = explode(".", $version);
$majorVersion = $versionParts[0].".".$versionParts[1];

$target = "/usr/local/share/groupoffice-" . $majorVersion;
$manageConfig = "/etc/groupoffice/manage/config.php";

echo "Updating sources\n";
exec("./update-git.sh");
$rsyncCmd = "rsync -av --delete --exclude=.git ../ " . $target;
echo "Running: " . $rsyncCmd . "\n";
system($rsyncCmd);

chdir("/usr/local/share/groupoffice-6.8/www/");
$upgradeCmd = "sudo -u www-data php cli.php core/System/upgrade -c=".$manageConfig;
echo "Running: " . $upgradeCmd . "\n";
system($upgradeCmd);

echo "Done\n\n";
