<?php
define('GO_CONFIG_FILE', "/etc/groupoffice/example.groupoffice.net/config.php");
require('/usr/share/groupoffice/GO.php');


$c = GO::getDbConnection();

$c->query("DELETE f1 FROM fs_folders f1, fs_folders f2 WHERE f1.id > f2.id AND f1.name = f2.name and f1.parent_id = f2.parent_id;");
$c->query("ALTER TABLE `fs_folders` ADD UNIQUE( `parent_id`, `name`);");

$sql = "DELETE  f
FROM fs_folders f
    left join fs_folders as parent
        on f.parent_id = parent.id
WHERE f.parent_id != 0 AND parent.id is NULL";

$count = 1;
while($count > 0) {
        $stmt = $c->query($sql);
        $count = $stmt->rowCount();
}

$c->query("ALTER TABLE `fs_folders` CHANGE `parent_id` `parent_id` INT(11) NULL DEFAULT NULL");

$c->query("INSERT INTO `fs_folders` (`user_id`, `id`, `parent_id`, `name`, `visible`, `acl_id`, `comment`, `thumbs`, `ctime`, `mtime`, `muser_id`, `quota_user_id`, `readonly`, `cm_state`, `apply_state`) VALUES ('1', 0, null, 'root', '0', '0', NULL, '1', '1', '1', '0', '0', '0', NULL, '0');");
$c->query("UPDATE fs_folders set id=0 where parent_id IS null and name='root'");

$c->query("ALTER TABLE `fs_folders` ADD FOREIGN KEY (`parent_id`) REFERENCES `fs_folders`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");


$c->query("DELETE fi FROM fs_files fi WHERE NOT EXISTS (SELECT * FROM fs_folders fo WHERE fo.id = fi.folder_id);");
$c->query("DELETE f1 FROM fs_files f1, fs_files f2 WHERE f1.id > f2.id AND f1.name = f2.name and f1.folder_id = f2.folder_id;");

$c->query("ALTER TABLE `fs_files` ADD UNIQUE( `folder_id`, `name`);");


$c->query("ALTER TABLE `fs_files` ADD INDEX(`folder_id`);");


$c->query("ALTER TABLE `fs_files` ADD  FOREIGN KEY (`folder_id`) REFERENCES `fs_folders`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;");

$c->query("DELETE c FROM go_search_cache c WHERE model_name = 'GO\\Files\\Model\\Folder' AND NOT EXISTS(SELECT * FROM fs_folders WHERE id=c.model_id);");
$c->query("DELETE c FROM go_search_cache c WHERE model_name = 'GO\\Files\\Model\\File' AND NOT EXISTS(SELECT * FROM fs_files WHERE id=c.model_id);");