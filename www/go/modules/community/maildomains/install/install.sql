create table community_maildomains_domain
(
    id           int(11) unsigned auto_increment
        primary key,
    userId       int                                 not null,
    domain       varchar(190)                        null,
    description  varchar(255)                        null,
    maxAliases   int unsigned      default 0         not null,
    maxMailboxes int unsigned      default 0         not null,
    totalQuota   bigint unsigned   default 0         not null,
    defaultQuota bigint unsigned   default 0         not null,
    transport    varchar(255)      default 'virtual' not null,
    backupMx     tinyint(1)        default 0         not null,
    spf          varchar(255)                        null,
    spfStatus    smallint unsigned default 0         not null,
    dmarc        varchar(255)                        null,
    dmarcStatus  smallint unsigned default 0         not null,
    mx           varchar(255)                        null,
    mxStatus     smallint unsigned default 0         not null,
    createdBy    int                                 not null,
    createdAt    datetime                            not null,
    modifiedBy   int                                 not null,
    modifiedAt   datetime                            null,
    active       tinyint(1)        default 1         not null,
    aclId        int                                 not null,
    constraint domain
        unique (domain),
    constraint community_maildomains_domain_acl_null_fk
        foreign key (aclId) references core_acl (id),
    constraint community_maildomains_domain_user_null_fk
        foreign key (userId) references core_user (id)
);

create table community_maildomains_alias
(
    id         int(11) unsigned auto_increment
        primary key,
    domainId   int(11) unsigned     not null,
    address    varchar(190)         null,
    goto       text                 null,
    createdBy  int                  not null,
    createdAt  datetime             not null,
    modifiedBy int                  not null,
    modifiedAt datetime             null,
    active     tinyint(1) default 1 not null,
    constraint address
        unique (address),
    constraint community_maildomains_alias_ibfk1
        foreign key (domainId) references community_maildomains_domain (id)
            on delete cascade
);

create index domainId
    on community_maildomains_alias (domainId);

create table community_maildomains_dkim_key
(
    domainId int(11) unsigned             not null,
    selector varchar(190)      default '' not null,
    publicKey      text              default '' not null,
    `privateKey`    text                         null,
    status   smallint unsigned default 0  null,
    enabled bool default false not null,
    primary key (selector, domainId),
    constraint domainSelector
        unique (domainId, selector),
    constraint community_maildomains_dkim_key_ibfk_1
        foreign key (domainId) references community_maildomains_domain (id)
            on delete cascade
);

create table community_maildomains_mailbox
(
    id          int(11) unsigned auto_increment
        primary key,
    domainId    int(11) unsigned           null,
    username    varchar(190)    default '' not null,
    password    varchar(255)    default '' not null,
    domainOwner bool default false not null,
    smtpAllowed tinyint(1)      default 0  not null,
    fts         tinyint(1)      default 0  not null,
    description        varchar(255)     null,
    maildir     varchar(255)               null,
    homedir     varchar(255)               null,
    quota       bigint unsigned default 0  not null,
    autoExpunge varchar(20) default '30d' not null comment 'Autoexpunge Spam and Trash folder after period',
    createdBy   int                        null,
    createdAt   datetime                   null,
    modifiedBy  int                        null,
    modifiedAt  datetime                   null,
    active      tinyint(1)      default 1  not null,
    bytes       bigint          default 0  not null,
    messages    int             default 0  not null,
    constraint username
        unique (username),
    constraint community_maildomains_mailbox_ibfk1
        foreign key (domainId) references community_maildomains_domain (id)
            on delete cascade
);



create view community_maildomains_dkim as
select
    concat(d.domain,'-',k.selector) as id,
    `d`.`domain`     AS `domain`,
    `k`.`selector`   AS `selector`,
    `k`.`privateKey` AS `privateKey`
from (`community_maildomains_dkim_key` `k` join `community_maildomains_domain` `d`
      on (`k`.`domainId` = `d`.`id`))
where k.enabled = true;
