<?php
$updates['201806260910'][] = "ALTER TABLE `ldapauth_server` CHANGE `imapHostname` `imapHostname` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
$updates['201806260910'][] = "ALTER TABLE `ldapauth_server` CHANGE `smtpHostname` `smtpHostname` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
$updates['201807260937'][] = "ALTER TABLE `ldapauth_server` ADD `username` VARCHAR(512) COLLATE ascii_bin NULL DEFAULT NULL AFTER `encryption`, ADD `password` VARCHAR(512) COLLATE ascii_bin NULL DEFAULT NULL AFTER `username`;";
$updates['201807260937'][] = "ALTER TABLE `ldapauth_server` CHANGE `smtpPassword` `smtpPassword` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL;";

$updates['201905241020'][] = "ALTER TABLE `ldapauth_server` CHANGE `removeDomainFromUsername` `loginWithEmail` TINYINT(1) NOT NULL DEFAULT '0';";
$updates['201905241020'][] = "ALTER TABLE `ldapauth_server` ADD `ldapVerifyCertificate` BOOLEAN NOT NULL DEFAULT TRUE AFTER `encryption`;";
