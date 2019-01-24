<?php
$updates["201309101354"][]="delete from dav_contacts where id not in (select id from ab_contacts);";

//datatype TEXT was not enough for the contact pictures of the iPhone
$updates['201310081641'][]="ALTER TABLE `dav_contacts` CHANGE COLUMN `data` `data` LONGTEXT NOT NULL ;";

$updates['201501221443'][]="ALTER TABLE `dav_contacts` CHANGE `uri` `uri` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL; ";

$updates['201604051221'][]="ALTER TABLE `dav_contacts` CHANGE COLUMN `data` `data` LONGTEXT NOT NULL ;";

$updates['201801150941'][]="update `dav_contacts` set data = replace(data, 'VERSION:5','VERSION:3.0');";

//final server update for old module
$updates['201901230836'][] = 'update core_module set package=\'community\', version=0, sort_order = sort_order + 100 where name=\'carddav\'';