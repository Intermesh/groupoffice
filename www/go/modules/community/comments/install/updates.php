<?php

$updates['201811061530'][] = 'RENAME TABLE `co_comments` TO `comments_comment`;';
$updates['201811061530'][] = 'RENAME TABLE `co_categories` TO `comments_label`;';

$updates['201811061530'][] = 'CREATE TABLE IF NOT EXISTS `comments_attachment` (
  `commentId` INT NOT NULL,
  `blobId` BINARY(40) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`commentId`, `blobId`),
  INDEX `fk_comments_attachment_comments_comment1_idx` (`commentId` ASC),
  INDEX `fk_comments_attachment_core_blob1_idx` (`blobId` ASC),
  CONSTRAINT `fk_comments_attachment_comments_comment1`
    FOREIGN KEY (`commentId`)
    REFERENCES `comments_comment` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_attachment_core_blob1`
    FOREIGN KEY (`blobId`)
    REFERENCES `core_blob` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;';

$updates['201811061530'][] = 'CREATE TABLE IF NOT EXISTS `comments_comment_label` (
  `labelId` INT NOT NULL,
  `commentId` INT NOT NULL,
  PRIMARY KEY (`labelId`, `commentId`),
  INDEX `fk_comments_label_has_comments_comment_comments_comment1_idx` (`commentId` ASC),
  INDEX `fk_comments_label_has_comments_comment_comments_label1_idx` (`labelId` ASC),
  CONSTRAINT `fk_comments_label_has_comments_comment_comments_label1`
    FOREIGN KEY (`labelId`)
    REFERENCES `comments_label` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_label_has_comments_comment_comments_comment1`
    FOREIGN KEY (`commentId`)
    REFERENCES `comments_comment` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;';

$updates['201811061530'][] = "ALTER TABLE `comments_label` 
ADD COLUMN `color` CHAR(6) NOT NULL DEFAULT '243a80' AFTER `name`;";

$updates['201811061530'][] = "ALTER TABLE `comments_comment` 
ADD COLUMN `createdAt` DATETIME NOT NULL AFTER `id`,
ADD COLUMN `modifiedBy` INT NULL AFTER `createdBy`,
ADD COLUMN `modifiedAt` DATETIME NULL AFTER `modifiedBy`,
CHANGE COLUMN `model_type_id` `entityTypeId` INT(11) NOT NULL ,
CHANGE COLUMN `model_id` `entityId` INT(11) NOT NULL ,
CHANGE COLUMN `user_id` `createdBy` INT(11) NULL ,
CHANGE COLUMN `comments` `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
ADD INDEX `fk_comments_comment_core_entity_type_idx` (`entityId` ASC),
ADD INDEX `fk_comments_comment_core_user1_idx` (`createdBy` ASC),
ADD INDEX `fk_comments_comment_core_user2_idx` (`modifiedBy` ASC),
DROP INDEX `link_id` ;";


$updates['201811061530'][] = 'SELECT createdAt, modifiedAt FROM comments_comment LIMIT 1;'; // <- ENSURE COLUMNS EXIST
	
$updates['201811061530'][] = function(){
  go()->getDbConnection()->exec("update comments_comment set createdBy = null where createdBy not in (select id from core_user)");
  go()->getDbConnection()->exec("update comments_comment set modifiedBy = null where modifiedBy not in (select id from core_user)");

  go()->getDbConnection()->exec("ALTER TABLE `comments_comment` 
ADD CONSTRAINT `fk_comments_comment_core_user1`
  FOREIGN KEY (`createdBy`)
  REFERENCES `core_user` (`id`)
  ON DELETE SET NULL
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_comments_comment_core_user2`
  FOREIGN KEY (`modifiedBy`)
  REFERENCES `core_user` (`id`)
  ON DELETE SET NULL
  ON UPDATE NO ACTION;");
};


$updates['201811061530'][] = 'UPDATE comments_comment SET createdAt = from_unixtime(ctime), modifiedAt = from_unixtime(mtime);';
$updates['201811061530'][] = 'INSERT INTO comments_comment_label (commentId, labelId) SELECT id, category_id FROM comments_comment WHERE category_id != 0;';

//cleanup
$updates['201811061530'][] = 'ALTER TABLE `comments_comment` 
DROP COLUMN `category_id`,
DROP COLUMN `mtime`,
DROP COLUMN `ctime`;';

$updates['201902051649'][] = "UPDATE comments_comment SET text = REPLACE(text, '\\n', '<br />');";

$updates['201906032000'][] = "ALTER TABLE `comments_comment` CHANGE `createdBy` `createdBy` INT(11) NULL;";


$updates['201906032000'][] = "ALTER TABLE `comments_comment` DROP FOREIGN KEY `fk_comments_comment_core_user1`";
$updates['201906032000'][] = "ALTER TABLE `comments_comment` ADD CONSTRAINT `fk_comments_comment_core_user1` FOREIGN KEY (`createdBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE NO ACTION";
$updates['201906032000'][] = "ALTER TABLE `comments_comment` DROP FOREIGN KEY `fk_comments_comment_core_user2`";
$updates['201906032000'][] = "ALTER TABLE `comments_comment` ADD CONSTRAINT `fk_comments_comment_core_user2` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE NO ACTION";

$updates['201907161437'][] = "";// "ALTER TABLE `comments_comment` ADD FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;"; Not yet because of unmgrated comments for companies.
$updates['201907161437'][] = "ALTER TABLE `comments_comment` ADD `section` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `text`;";
$updates['201907161437'][] = "ALTER TABLE `comments_comment` ADD INDEX(`section`);";


$updates['202003261139'][] = "CREATE TABLE `comments_comment_image` (
`commentId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['202003261139'][] = "ALTER TABLE `comments_comment_image`
  ADD PRIMARY KEY (`commentId`,`blobId`),
  ADD KEY `blobId` (`blobId`);";

$updates['202003261139'][] = "ALTER TABLE `comments_comment_image`
  ADD CONSTRAINT `comments_comment_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `comments_comment_image_ibfk_2` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE;";

$updates['202003261139'][] = function() {
	$notes = \go\modules\community\comments\model\Comment::find()->where('text', 'LIKE', '%<img%');
	foreach($notes as $note) {
		try {
			$note->save();
		}
		catch(\Exception $e) {
			echo "Error saving comment: " . $e->getMessage() ."\n";
		}
	}
};

$updates['202011161602'][] = "ALTER TABLE `comments_comment` ADD `date` DATETIME NULL AFTER `createdAt`;";
$updates['202011161602'][] = "update `comments_comment` set `date` = createdAt;";
$updates['202011161602'][] = "ALTER TABLE `comments_comment` ADD INDEX(`date`);";

$updates['202111041557'][] = "drop table comments_attachment";

$updates['202111041557'][] = "create table comments_comment_attachment
(
	id        int unsigned auto_increment
        primary key,
    commentId int          not null,
    blobId    binary(40)   null,
    name      varchar(190) not null,
    constraint comments_comment_attachment_comments_comment_id_fk
        foreign key (commentId) references comments_comment (id)
            on update cascade,
    constraint comments_comment_attachment_core_blob_id_fk
        foreign key (blobId) references core_blob (id)
);";
