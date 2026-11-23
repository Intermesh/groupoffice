<?php

// The subject (user ID) at the identity provider. Users are found by it instead of by their e-mail address.
$updates['202611111200'][] = "alter table oidc_user
    add sub varchar(255) collate ascii_bin null;";

$updates['202611111200'][] = "create unique index oidc_user_clientId_sub_uindex
    on oidc_user (clientId, sub);";
