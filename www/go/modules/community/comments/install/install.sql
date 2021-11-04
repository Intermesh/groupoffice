
CREATE TABLE `comments_comment` (
  `id` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `date` DATETIME NOT NULL,
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `text` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments_comment_label` (
  `labelId` int(11) NOT NULL,
  `commentId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments_label` (
  `id` int(11) NOT NULL,
  `name` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '243a80'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



ALTER TABLE `comments_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comments_comment_core_entity_type_idx` (`entityId`),
  ADD KEY `fk_comments_comment_core_user1_idx` (`createdBy`),
  ADD KEY `fk_comments_comment_core_user2_idx` (`modifiedBy`);

ALTER TABLE `comments_comment_label`
  ADD PRIMARY KEY (`labelId`,`commentId`),
  ADD KEY `fk_comments_label_has_comments_comment_comments_comment1_idx` (`commentId`),
  ADD KEY `fk_comments_label_has_comments_comment_comments_label1_idx` (`labelId`);

ALTER TABLE `comments_label`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `comments_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `comments_label`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `comments_comment`
  ADD CONSTRAINT `fk_comments_comment_core_user1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comments_comment_core_user2` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `comments_comment_label`
  ADD CONSTRAINT `fk_comments_label_has_comments_comment_comments_comment1` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comments_label_has_comments_comment_comments_label1` FOREIGN KEY (`labelId`) REFERENCES `comments_label` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;


ALTER TABLE `comments_comment` ADD FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE `comments_comment` ADD INDEX(`section`);



CREATE TABLE `comments_comment_image` (
  `commentId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `comments_comment_image`
  ADD PRIMARY KEY (`commentId`,`blobId`),
  ADD KEY `blobId` (`blobId`);

ALTER TABLE `comments_comment_image`
  ADD CONSTRAINT `comments_comment_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `comments_comment_image_ibfk_2` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE;

  ALTER TABLE `comments_comment` ADD INDEX(`date`);


create table comments_comment_attachment
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
);
