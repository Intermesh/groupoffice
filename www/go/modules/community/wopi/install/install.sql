CREATE TABLE `wopi_action` (
  `serviceId` int(11) NOT NULL,
  `app` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ext` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wopi_lock` (
  `id` varchar(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `serviceId` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  `expiresAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wopi_service` (
  `id` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aclId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wopiClientUri` TEXT NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wopi_token` (
  `id` int(11) NOT NULL,
  `serviceId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `token` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiresAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `wopi_action`
  ADD KEY `serviceId` (`serviceId`);

ALTER TABLE `wopi_lock`
  ADD PRIMARY KEY (`id`,`serviceId`),
  ADD KEY `fileId` (`fileId`),
  ADD KEY `expiresAt` (`expiresAt`);

ALTER TABLE `wopi_service`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE `type` (`type`);

ALTER TABLE `wopi_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serviceId` (`serviceId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `token` (`token`),
  ADD KEY `expiresAt` (`expiresAt`);


ALTER TABLE `wopi_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `wopi_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `wopi_action`
  ADD CONSTRAINT `wopi_action_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `wopi_service` (`id`) ON DELETE CASCADE;

ALTER TABLE `wopi_lock`
  ADD CONSTRAINT `wopi_lock_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `wopi_service` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wopi_lock_ibfk_2` FOREIGN KEY (`fileId`) REFERENCES `fs_files` (`id`) ON DELETE CASCADE;

ALTER TABLE `wopi_token`
  ADD CONSTRAINT `wopi_token_ibfk_2` FOREIGN KEY (`serviceId`) REFERENCES `wopi_service` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wopi_token_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;


alter table wopi_service add constraint wopi_service_core_acl_id_fk foreign key (aclId) references core_acl (id);