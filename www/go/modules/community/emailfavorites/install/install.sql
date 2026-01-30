CREATE TABLE IF NOT EXISTS `em_folders_favorites`
(
    `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255)     NOT NULL,
    `account_id` INT(11)          NOT NULL,
    `mailbox`    VARCHAR(255)     NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;