CREATE TABLE IF NOT EXISTS `community_maildomains_domain`
(
    `id`           int(11) UNSIGNED                                              NOT NULL AUTO_INCREMENT,
    `userId`       int(11)                                                       NOT NULL,
    `domain`       varchar(190)                                                           default NULL,
    `description`  varchar(255)                                                           default NULL,
    `maxAliases`   int(10) UNSIGNED                                              NOT NULL default '0',
    `maxMailboxes` int(10) UNSIGNED                                              NOT NULL default '0',
    `totalQuota`   bigint(20) UNSIGNED                                           NOT NULL default '0',
    `defaultQuota` bigint(20) UNSIGNED                                           NOT NULL default '0',
    `transport`    VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'virtual',
    `backupMx`     tinyint(1)                                                    NOT NULL default '0',
    `spf`          VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `spfStatus` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
    `dmarc`        VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `dmarcStatus` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
    `mx`           VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `mxStatus` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
    `createdBy`    INT(11)                                                       NOT NULL,
    `createdAt`    DATETIME                                                      NOT NULL,
    `modifiedBy`   int(11)                                                       NOT NULL,
    `modifiedAt`   DATETIME                                                               DEFAULT NULL,
    `active`       BOOLEAN                                                       NOT NULL DEFAULT '1',
    `aclId`        int(11)                                                       NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `domain` (`domain`),
    CONSTRAINT `community_maildomains_domain_acl_null_fk`
        FOREIGN KEY (aclId) REFERENCES `core_acl` (`id`),
    CONSTRAINT `community_maildomains_domain_user_null_fk`
        FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `community_maildomains_alias`
(
    `id`         int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `domainId`   int(11) UNSIGNED NOT NULL,
    `address`    varchar(190)              default NULL,
    `goto`       text,
    `createdBy`  INT(11)          NOT NULL,
    `createdAt`  DATETIME         NOT NULL,
    `modifiedBy` int(11)          NOT NULL,
    `modifiedAt` DATETIME                  DEFAULT NULL,
    `active`     tinyint(1)        NOT NULL default '1',
    PRIMARY KEY (`id`),
    UNIQUE `address` (`address`),
    KEY `domainId` (`domainId`),
    CONSTRAINT `community_maildomains_alias_ibfk1` FOREIGN KEY (`domainId`)
        REFERENCES `community_maildomains_domain`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `community_maildomains_mailbox`
(
    `id`          int(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `domainId`    int(11) UNSIGNED    NOT NULL,
    `username`    varchar(190)        NOT NULL DEFAULT '',
    `password`    varchar(255)        NOT NULL DEFAULT '',
    `smtpAllowed` bool                NOT NULL DEFAULT FALSE,
    `fts`         bool                NOT NULL DEFAULT FALSE,
    `name`        varchar(255)        NOT NULL DEFAULT '',
    `maildir`     varchar(255)                 default NULL,
    `homedir`     VARCHAR(255)                 default NULL,
    `quota`       bigint(20) UNSIGNED NOT NULL default '0',
    `createdBy`   INT(11)             NOT NULL,
    `createdAt`   DATETIME            NOT NULL,
    `modifiedBy`  int(11)             NOT NULL,
    `modifiedAt`  DATETIME                     DEFAULT NULL,
    `active`      BOOLEAN             NOT NULL DEFAULT '1',
    bytes bigint not null default 0,
    messages integer not null default 0,
    PRIMARY KEY (`id`),
    UNIQUE `username` (`username`),
    CONSTRAINT `community_maildomains_mailbox_ibfk1` FOREIGN KEY (`domainId`)
        REFERENCES `community_maildomains_domain`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4 COLLATE=utf8mb4_unicode_ci;

create table community_maildomains_dkim_key
(
    domainId int(11) unsigned             not null,
    selector varchar(190)      default '' not null,
    txt      text              default '' not null,
    `key`    text                         null,
    status   smallint unsigned default 0  null,
    primary key (selector, domainId),
    constraint domainSelector
        unique (domainId, selector),
    constraint community_maildomains_dkim_key_ibfk_1
        foreign key (domainId) references community_maildomains_domain (id)
            on delete cascade
);

CREATE TABLE community_maildomains_quota (
   username varchar(190) not null,
   bytes bigint not null default 0,
   messages integer not null default 0,
   primary key (username)
);

alter table community_maildomains_quota
    add constraint community_maildomains_quota_mailbox_username_fk
        foreign key (username) references community_maildomains_mailbox (username)
            on delete cascade;