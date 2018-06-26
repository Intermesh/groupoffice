<?php
$updates['201806260910'][] = "ALTER TABLE `ldapauth_server` CHANGE `imapHostname` `imapHostname` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
$updates['201806260910'][] = "ALTER TABLE `ldapauth_server` CHANGE `smtpHostname` `smtpHostname` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
