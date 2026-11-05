create table oidc_client
(
    id           int unsigned auto_increment
        primary key,
    name         varchar(50)  not null,
    url          varchar(190) not null,
    clientId     varchar(190) not null,
    clientSecret varchar(190) not null,
    constraint oidc_client_pk
        unique (name)
);

create table oidc_user
(
    clientId int unsigned not null,
    userId   int          not null,
    primary key (userId, clientId),
    constraint oidc_user_core_user_id_fk
        foreign key (userId) references core_user (id)
            on delete cascade,
    constraint oidc_user_oidc_client_id_fk
        foreign key (clientId) references oidc_client (id)
            on delete cascade
);

