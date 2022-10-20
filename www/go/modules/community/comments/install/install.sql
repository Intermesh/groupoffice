create table comments_comment
(
    id            int auto_increment
        primary key,
    createdAt     datetime                       not null,
    date          datetime                       not null,
    entityId      int                            not null,
    entityTypeId  int                            not null,
    createdBy     int                            null,
    modifiedBy    int                            null,
    modifiedAt    datetime                       null,
    text          mediumtext charset utf8mb4     null,
    section       varchar(50)                    null,
    mimeMessageId varchar(255) collate ascii_bin null,
    constraint fk_comments_comment_core_user1
        foreign key (createdBy) references core_user (id)
            on delete set null,
    constraint fk_comments_comment_core_user2
        foreign key (modifiedBy) references core_user (id)
            on delete set null
);

create index date
    on comments_comment (date);

create index fk_comments_comment_core_entity_type_idx
    on comments_comment (entityId);

create index fk_comments_comment_core_user1_idx
    on comments_comment (createdBy);

create index fk_comments_comment_core_user2_idx
    on comments_comment (modifiedBy);

create index section
    on comments_comment (section);

create table comments_comment_attachment
(
    id        int unsigned auto_increment
        primary key,
    commentId int          not null,
    blobId    binary(40)   null,
    name      varchar(190) not null,
    constraint comments_comment_attachment_comments_comment_id_fk
        foreign key (commentId) references comments_comment (id)
            on delete cascade,
    constraint comments_comment_attachment_core_blob_id_fk
        foreign key (blobId) references core_blob (id)
            on delete cascade
);

create table comments_comment_image
(
    commentId int        not null,
    blobId    binary(40) not null,
    primary key (commentId, blobId),
    constraint comments_comment_image_ibfk_1
        foreign key (blobId) references core_blob (id),
    constraint comments_comment_image_ibfk_2
        foreign key (commentId) references comments_comment (id)
            on delete cascade
);

create index blobId
    on comments_comment_image (blobId);

create table comments_label
(
    id    int auto_increment
        primary key,
    name  varchar(127) default ''       not null,
    color char(6)      default '243a80' not null
);

create table comments_comment_label
(
    labelId   int not null,
    commentId int not null,
    primary key (labelId, commentId),
    constraint fk_comments_label_has_comments_comment_comments_comment1
        foreign key (commentId) references comments_comment (id)
            on delete cascade,
    constraint fk_comments_label_has_comments_comment_comments_label1
        foreign key (labelId) references comments_label (id)
            on delete cascade
);

create index fk_comments_label_has_comments_comment_comments_comment1_idx
    on comments_comment_label (commentId);

create index fk_comments_label_has_comments_comment_comments_label1_idx
    on comments_comment_label (labelId);



alter table comments_comment
    add constraint comments_comment_core_entity_id_fk
        foreign key (entityTypeId) references core_entity (id)
            on delete cascade;
